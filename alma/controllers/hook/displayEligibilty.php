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
use Alma\API\RequestError;
use Alma\API\Endpoints\Results\Eligibility;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaAdminHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaEligibilityHelper.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';

class AlmaDisplayEligibiltyController extends AlmaAdminHookController
{
    public function run($params)
    {
        if(!array_key_exists('product', $params)) {
            return '';
        }

        $product = $params['product'];
        $tpl = $this->context->smarty->createTemplate(
            _PS_ROOT_DIR_ . $this->module->_path . 'views/templates/hook/displayEligibilty.tpl'
        );

        $this->context->controller->addCSS($this->module->_path . 'views/css/product.css', 'all');
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->addCSS($this->module->_path . 'views/css/modal.css', 'all');
        }

        if (is_callable('Media::getMediaPath')) {
            $logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/alma_logo.svg');
        } else {
            $logo = $this->module->getPathUri() . '/views/img/alma_logo.svg';
        }

        $eligibility = AlmaEligibilityHelper::eligibilityProduct($product);
        $isEligible = false;
        if ($eligibility instanceof Eligibility) {
            $isEligible = $eligibility->isEligible();
        } else if (is_array($eligibility)) {
            foreach ($eligibility as $eli) {
                if (true == $eli->isEligible()) {
                     $isEligible = true;
                     break;
                }
            }
        }

        if ($isEligible) {

            $installmentsCount = $plans = [];
            $n = 2;
            while ($n < AlmaSettings::installmentPlansMaxN()) {
                ++$n;
                if (!AlmaSettings::isInstallmentPlanEnabled($n)) {
                    continue;
                } else {
                    $installment = $eligibility[$n];
                    $installmentsCount[] = $n;
                    $plan = $installment->getPaymentPlan();
                    $diff = $plan[0]['purchase_amount'] != $plan[1]['purchase_amount'] ? true : false;
                    $fee = $plan[0]['customer_fee'] ? Tools::displayPrice(almaPriceFromCents($plan[0]['customer_fee'])) : null;
                    $feeMessage = $fee ? ' ' . sprintf($this->module->l('(with fee: %s)'), $fee) : '';
                    $message = '';

                    if (2 == $n) {
                        if ($diff) {
                            $firstInstallmentMessage = Tools::displayPrice(almaPriceFromCents($plan[0]['purchase_amount']));
                            $otherInstallmentsMessage = sprintf($this->module->l('and %s'), Tools::displayPrice(almaPriceFromCents($plan[1]['purchase_amount'])));
                        } else  {
                            $firstInstallmentMessage = '2x ' . Tools::displayPrice(almaPriceFromCents($plan[0]['purchase_amount']));
                            $otherInstallmentsMessage = '';
                        }
                    } else if ($n > 2) {
                        if ($plan[0] != $plan[1]) {
                            $firstInstallmentMessage = Tools::displayPrice(almaPriceFromCents($plan[0]['purchase_amount']));
                            $otherInstallmentsMessage = sprintf($this->module->l('and %s'), ($n - 1) . 'x ' . Tools::displayPrice(almaPriceFromCents($plan[1]['purchase_amount'])));
                        } else {
                            $firstInstallmentMessage = Tools::displayPrice(almaPriceFromCents($plan[0]['purchase_amount']));
                            $otherInstallmentsMessage = $n . 'x ' . Tools::displayPrice(almaPriceFromCents($plan[0]['purchase_amount']));
                        }
                    }
                    $message = $firstInstallmentMessage . ' ' . $otherInstallmentsMessage;
                    $message .= $feeMessage ? '<br /><span class="alma-text-normal alma-text-grey">' . $feeMessage . '</span>' : '';

                    $plans[] = array(
                        'installments' => $n,
                        'message' => $message,
                    );

                }
            }

            $tpl->assign(
                array(
                    'isEligible' => true,
                    'plans' => $plans,
                    'installments' => implode(' ' . $this->module->l('or') . ' ', $installmentsCount),
                    'logo' => $logo,
                )
            );

        } else {

            $purchaseMin = $eligibility->constraints['purchase_amount']['minimum'];
            $purchaseMax = $eligibility->constraints['purchase_amount']['maximum'];

            $n = 1;
            while ($n < AlmaSettings::installmentPlansMaxN()) {
                ++$n;
                if (!AlmaSettings::isInstallmentPlanEnabled($n)) {
                    continue;
                } else {
                    $min = AlmaSettings::installmentPlanMinAmount($n);
                    $purchaseMin = min($min, $purchaseMin);

                    $max = AlmaSettings::installmentPlanMaxAmount($n);
                    $purchaseMax = max($max, $purchaseMax);
                }
            }
            $tpl->assign(
                array(
                    'isEligible' => false,
                    'purchaseMin' => almaPriceFromCents($purchaseMin),
                    'purchaseMax' => almaPriceFromCents($purchaseMax),
                    'logo' => $logo,
                )
            );

        }
        return $tpl->fetch();
    }
}
