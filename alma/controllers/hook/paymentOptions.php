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

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaProtectedHookController.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/PaymentData.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaClient.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/CartData.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaEligibilityHelper.php';

class AlmaPaymentOptionsController extends AlmaProtectedHookController
{
    public function run($params)
    {
		// Check if some products in cart are in the excludes listing
		$diff = CartData::getCartExclusion($params['cart']);

		if (!empty($diff)) {
			return [];
		}

		$installmentPlans = AlmaEligibilityHelper::eligibilityCheck($this->context);
        $options = [];

        foreach($installmentPlans as $plan){
            if(!$plan->isEligible){
                continue;
            }
            $n = $plan->installmentsCount;
            $forEUComplianceModule = false;
            if (array_key_exists('for_eu_compliance_module', $params)) {
                $forEUComplianceModule = $params['for_eu_compliance_module'];
            }
            $paymentOption = $this->createPaymentOption(
                $forEUComplianceModule,
                sprintf(AlmaSettings::getPaymentButtonTitle(), $n),
                $this->context->link->getModuleLink($this->module->name, 'payment', array('n' => $n), true),
                $n
            );
            if (!$forEUComplianceModule && !empty(AlmaSettings::getPaymentButtonDescription())) {
                $this->context->smarty->assign(array(
                    'desc' => sprintf(AlmaSettings::getPaymentButtonDescription(), $n),
                    'plans' => (array) $plan->paymentPlan,
                ));

                $template = $this->context->smarty->fetch(
                    "module:{$this->module->name}/views/templates/hook/payment_button_desc.tpl"
                );

                $paymentOption->setAdditionalInformation($template);
            }
             $options[] = $paymentOption;
        }

        return $options;
    }

    private function createPaymentOption($forEUComplianceModule, $ctaText, $action, $n)
    {
        $baseDir = _PS_MODULE_DIR_ . $this->module->name;

        if ($forEUComplianceModule) {
            $logo = Media::getMediaPath($baseDir . '/views/img/alma_payment_logos.svg');
            $paymentOption = array(
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo
            );
        } else {
            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $logo = Media::getMediaPath($baseDir . '/views/img/logos/alma_p'.$n.'x.svg');
            $paymentOption
                ->setModuleName($this->module->name)
                ->setCallToActionText($ctaText)
                ->setAction($action)
                ->setLogo($logo);
        }

        return $paymentOption;
    }
}
