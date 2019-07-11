<?php
/**
 * 2018-2019 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/PaymentData.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';

use Alma\API\RequestError;

class AlmaEligibilityHelper
{
    private static function checkPnXBounds($cart) {
        $purchaseAmount = almaPriceToCents((float) $cart->getordertotal(true, Cart::BOTH));
        $globalMin = PHP_INT_MAX;
        $globalMax = 0;

        $n = 1;
        while ($n < AlmaSettings::installmentPlansMaxN()) {
            ++$n;

            if (!AlmaSettings::isInstallmentPlanEnabled($n)) {
                continue;
            } else {
                $min = AlmaSettings::installmentPlanMinAmount($n);
                $globalMin = min($min, $globalMin);

                $max = AlmaSettings::installmentPlanMaxAmount($n);
                $globalMax = max($max, $globalMax);

                if ($purchaseAmount >= $min && $purchaseAmount < $max) {
                    return true;
                }
            }
        }

        return array($globalMin, $globalMax);
    }

    public static function eligibilityCheck($context)
    {
        $pnxBounds = self::checkPnXBounds($context->cart);
        // If we got an array, then the cart is not eligible because not within the returned bounds
        if (is_array($pnxBounds)) {
            // Mock Alma's Eligibility object
            $eligibility = new stdClass();
            $eligibility->isEligible = false;
            $eligibility->constraints = array(
                "purchase_amount" => array(
                    "minimum" => $pnxBounds[0],
                    "maximum" => $pnxBounds[1]
                )
            );

            return $eligibility;
        }

        $paymentData = PaymentData::dataFromCart($context->cart, $context);
        if (!$paymentData) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return null;
        }

        $alma = AlmaClient::defaultInstance();
        if (!$alma) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no API client');

            return null;
        }

        $eligibility = null;

        try {
            $eligibility = $alma->payments->eligibility($paymentData);
        } catch (RequestError $e) {
            AlmaLogger::instance()->error(
                "Error when checking cart {$context->cart->id} eligibility: " . $e->getMessage()
            );

            return null;
        }

        return $eligibility;
    }
}
