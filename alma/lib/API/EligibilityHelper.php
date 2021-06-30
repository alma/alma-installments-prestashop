<?php
/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\API;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\RequestError;
use Alma\PrestaShop\Model\PaymentData;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use Cart;

class EligibilityHelper
{
    public static function eligibilityCheck($context)
    {
        $eligibilities = [];
        $activePlans = [];
        $almaEligibilities = [];
        $purchaseAmount = almaPriceToCents($context->cart->getOrderTotal(true, Cart::BOTH));
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            Logger::instance()->error('Cannot check cart eligibility: no API client');

            return [];
        }

        $feePlans = json_decode(Settings::getFeePlans());

        if (!$feePlans) {
            return [];
        }

        foreach ($feePlans as $key => $feePlan) {
            $getDataFromKey = Settings::getDataFromKey($key);

            if (1 == $feePlan->enabled) {
                if ($purchaseAmount < $feePlan->min
                || $purchaseAmount > $feePlan->max) {
                    $eligibility = new Eligibility(
                        [
                            'installments_count' => $getDataFromKey['installmentsCount'],
                            'deferred_days' => $getDataFromKey['deferredDays'],
                            'deferred_months' => $getDataFromKey['deferredMonths'],
                            'eligible' => false,
                            'constraints' => [
                                'purchase_amount' => [
                                    'minimum' => $feePlan->min,
                                    'maximum' => $feePlan->max,
                                ],
                            ],
                        ]
                    );
                    $eligibilities[] = $eligibility;
                } else {
                    //$activePlans[] = (int) $getDataFromKey['installmentsCount'];
                    $activePlans[] = $getDataFromKey;
                }
            }
        }

        $paymentData = PaymentData::dataFromCart($context->cart, $context, $activePlans);
        if (!$paymentData) {
            Logger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return [];
        }
        try {
            if (!empty($activePlans)) {
                $almaEligibilities = $alma->payments->eligibility($paymentData);
            }
        } catch (RequestError $e) {
            Logger::instance()->error(
                "Error when checking cart {$context->cart->id} eligibility: " . $e->getMessage()
            );

            return [];
        }

        $eligibilities = array_merge((array) $eligibilities, (array) $almaEligibilities);
        usort($eligibilities, function ($a, $b) {
            return $a->installmentsCount - $b->installmentsCount;
        });

        return $eligibilities;
    }
}
