<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
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
use Alma\API\Endpoints\Results\Eligibility;

class AlmaEligibilityHelper
{

    public static function eligibilityCheck($context)
    {
        $eligibilities = array();
        $activePlans = array();
        $almaEligibilities = array();
        $purchaseAmount = almaPriceToCents($context->cart->getOrderTotal(true, Cart::BOTH));
        $alma = AlmaClient::defaultInstance();
        if (!$alma) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no API client');
            return array();
        }
        
        if(0 === AlmaSettings::installmentPlansMaxN()){            
            return array();
        }
                        
        foreach(AlmaSettings::activeInstallmentsCounts() as $n){
            if ($purchaseAmount < AlmaSettings::installmentPlanMinAmount($n) || $purchaseAmount > AlmaSettings::installmentPlanMaxAmount($n)) {                    
                $eligibility = new Eligibility(
                    array(
                        'installments_count' => $n,
                        'eligible' => false,
                        'constraints' => array(
                            'purchase_amount' => array(
                                'minimum' => AlmaSettings::installmentPlanMinAmount($n),
                                'maximum' => AlmaSettings::installmentPlanMaxAmount($n)
                            )
                        )
                        
                    )
                );
                $eligibilities[] = $eligibility;
            } else {
                $activePlans[] = $n;
            }
        }

        $paymentData = PaymentData::dataFromCart($context->cart, $context, $activePlans);
        if (!$paymentData) {
            AlmaLogger::instance()->error('Cannot check cart eligibility: no data extracted from cart');
            return array();
        }
        try {
            if(!empty($activePlans)){
                $almaEligibilities = $alma->payments->eligibility($paymentData);
            }            
        } catch (RequestError $e) {
            AlmaLogger::instance()->error(
                "Error when checking cart {$context->cart->id} eligibility: " . $e->getMessage()
            );
            return array();
        }        
        
        $eligibilities = array_merge((array) $eligibilities, (array) $almaEligibilities);
        usort($eligibilities, array("AlmaEligibilityHelper", "cmp_installments_count"));

        return $eligibilities;
    }

    public static function cmp_installments_count($a, $b)
    {
        return $a->installmentsCount > $b->installmentsCount ? 1 : ($a->installmentsCount == $b->installmentsCount ? 0 : -1);
    }
}
