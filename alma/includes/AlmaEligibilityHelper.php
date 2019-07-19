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
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaProduct.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/PaymentData.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';

use Alma\API\RequestError;
use Alma\API\Endpoints\Results\Eligibility;

class AlmaEligibilityHelper
{
    private static function checkPnXBounds($amount) {
        $purchaseAmount = almaPriceToCents($amount);
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

    protected static function checkEligibilityCart(Cart $cart)
    {
        $products = $cart->getProducts();
        $idProducts = [];
        foreach ($products as $product) {
            $idProducts[] = $product['id_product'];
        }
        $categoriesExludes = AlmaSettings::getExcludeCategories();
        if ($categoriesExludes && count($categoriesExludes) > 0) {
            foreach ($categoriesExludes as $category) {
                if (AlmaProduct::productIsInCategory($idProducts, $category)) {
                    $eligibility = new Eligibility();
                    $eligibility->setIsEligible(false);
                    $eligibility->setReasons('unavailable');

                    return $eligibility;
                }
            }
        }
        return null;
    }

    public static function eligibilityCheck($context)
    {
        $pnxBounds = self::checkPnXBounds((float) $context->cart->getordertotal(true, Cart::BOTH));
        // If we got an array, then the cart is not eligible because not within the returned bounds
        if (is_array($pnxBounds)) {
            $eligibility = new Eligibility();
            $eligibility->setIsEligible(false);
            $eligibility->setCnstraints(array(
                'purchase_amount' => array(
                    'minimum' => $pnxBounds[0],
                    'maximum' => $pnxBounds[1]
                )
            ));

            return $eligibility;
        }

        $installments = [];
        $n = 1;
        while ($n < AlmaSettings::installmentPlansMaxN()) {
            ++$n;
            if (!AlmaSettings::isInstallmentPlanEnabled($n)) {
                continue;
            } else {
                $installments[] = $n;
            }
        }

        $paymentData = PaymentData::dataFromCart($context->cart, $context);
        if (!$paymentData) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return null;
        }
        $paymentData['payment']['installments_count'] = $installments;

        $eligibility = self::checkEligibilityCart($context->cart);

        if ($eligibility instanceof Eligibility) {
            return $eligibility;
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

    public static function eligibilityProduct(Product $product)
    {
        $categoriesExludes = AlmaSettings::getExcludeCategories();
        if ($categoriesExludes && count($categoriesExludes) > 0) {print_r($categoriesExludes);
            foreach ($categoriesExludes as $category) {
                if (AlmaProduct::productIsInCategory($produc->id_product, $category)) {
                    $eligibility = new Eligibility();
                    $eligibility->setIsEligible(false);
                    $eligibility->setReasons('unavailable');

                    return $eligibility;
                }
            }
        }

        $id_product_attribute = 0;
        $purchaseAmount = (float) $product->getPrice(true, $id_product_attribute);
        $pnxBounds = self::checkPnXBounds($purchaseAmount);
        // If we got an array, then the product is not eligible because not within the returned bounds
        if (is_array($pnxBounds)) {
            $eligibility = new Eligibility();
            $eligibility->setIsEligible(false);
            $eligibility->setConstraints(array(
                'purchase_amount' => array(
                    'minimum' => $pnxBounds[0],
                    'maximum' => $pnxBounds[1]
                )
            ));

            return $eligibility;
        }

        $installments = [];
        $n = 1;
        while ($n < AlmaSettings::installmentPlansMaxN()) {
            ++$n;
            if (!AlmaSettings::isInstallmentPlanEnabled($n)) {
                continue;
            } else {
                $installments[] = $n;
            }
        }

        $paymentData = array(
            'payment' => array(
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'installments_count' => $installments,
            ),
        );
        if (!$paymentData) {
            AlmaLogger::instance()->error('Cannot check product eligibility: no data price from product');

            return null;
        }

        $alma = AlmaClient::defaultInstance();
        if (!$alma) {
            AlmaLogger::instance()->error('Cannot check product eligibility: no API client');

            return null;
        }

        $eligibility = null;

        try {
            $eligibility = $alma->payments->eligibility($paymentData);
        } catch (RequestError $e) {
            AlmaLogger::instance()->error(
                "Error when checking product {$product->cart->id} eligibility: " . $e->getMessage()
            );

            return null;
        }

        return $eligibility;
    }
}
