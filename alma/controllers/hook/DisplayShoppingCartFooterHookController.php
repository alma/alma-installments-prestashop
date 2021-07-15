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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\API\EligibilityHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Utils\LocaleHelper;
use Alma\PrestaShop\Utils\Settings;
use Cart;
use Media;

final class DisplayShoppingCartFooterHookController extends FrontendHookController
{
    public function canRun()
    {
        return parent::canRun() && Settings::showEligibilityMessage();
    }

    public function run($params)
    {
        $eligibilityMsg = null;

        $activePlans = Settings::activePlans(true);

        if (!$activePlans) {
            return;
        }

        $cart = $this->context->cart;
        $cartTotal = almaPriceToCents((float) $cart->getOrderTotal(true, Cart::BOTH));

        $isEligible = true;
        if (!Settings::showCartWidgetIfNotEligible()) {
            $installmentPlans = EligibilityHelper::eligibilityCheck($this->context);
            $isEligible = false;
            foreach ($installmentPlans as $plan) {
                if ($plan->installmentsCount !== 1 && $plan->isEligible) {
                    $isEligible = true;
                    break;
                }
            }
        }

        // Check if some products in cart are in the excludes listing
        $isExcluded = false;
        $diff = CartData::getCartExclusion($params['cart']);
        if (!empty($diff)) {
            $eligibilityMsg = Settings::getNonEligibleCategoriesMessage();
            $isExcluded = true;
        }

        if (is_callable('Media::getMediaPath')) {
            $logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/logo_alma.svg');
        } else {
            $logo = $this->module->getPathUri() . '/views/img/logos/logo_alma.svg';
        }

        // need ps verions && refresh price
        $psVersion = '1.7';
        $refreshPrice = true;
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $psVersion = '1.6';
            $refreshPrice = false;
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $psVersion = '1.5';
            }
        }

        if ($isEligible) {
            $this->context->smarty->assign([
                'eligibility_msg' => $eligibilityMsg,
                'logo' => $logo,
                'isExcluded' => $isExcluded,
                'settings' => [
                    'merchantId' => Settings::getMerchantId(),
                    'apiMode' => Settings::getActiveMode(),
                    'amount' => $cartTotal,
                    'plans' => $activePlans,
                    'refreshPrice' => $refreshPrice,
                    'decimalSeparator' => LocaleHelper::decimalSeparator(),
                    'thousandSeparator' => LocaleHelper::thousandSeparator(),
                    'psVersion' => $psVersion,
                ],
            ]);

            return $this->module->display($this->module->file, 'displayShoppingCartFooter.tpl');
        } else {
            return;
        }
    }
}
