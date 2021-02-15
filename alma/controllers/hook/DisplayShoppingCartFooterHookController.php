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

use Cart;
use Media;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Utils\Settings;
use Alma\PrestaShop\Utils\LocaleHelper;
use Alma\PrestaShop\API\EligibilityHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;

final class DisplayShoppingCartFooterHookController extends FrontendHookController
{
    public function canRun()
    {
        return parent::canRun() && Settings::showEligibilityMessage();
    }

    public function run($params)
    {
        $eligibilities = EligibilityHelper::eligibilityCheck($this->context);
        $eligible = false;
        foreach ($eligibilities as $eligibility) {
            if ($eligibility->isEligible) {
                $eligible = true;
                break;
            }
        }
        $cart = $this->context->cart;
        $cartTotal = almaPriceToCents((float) $cart->getOrderTotal(true, Cart::BOTH));
        if (empty($eligibilities)) {
            $eligibilityMsg = Settings::getNonEligibilityMessage();
        } elseif (!$eligible) {


            $minimum = PHP_INT_MAX;
            $maximum = 0;

            foreach ($eligibilities as $eligibility) {
                $minAmount = $eligibility->constraints['purchase_amount']['minimum'];
                $maxAmount = $eligibility->constraints['purchase_amount']['maximum'];
                if ($cartTotal < $minAmount || $cartTotal > $maxAmount) {
                    if ($cartTotal > $maxAmount && $maxAmount > $maximum) {
                        $maximum = $maxAmount;
                    }
                    if ($cartTotal < $minAmount && $minAmount < $minimum) {
                        $minimum = $minAmount;
                    }
                }
            }

            $eligibilityMsg = '';
            if ($cartTotal > $maximum && $maximum != 0) {
                $eligibilityMsg = ' ' . Settings::getNonEligibilityMaxAmountMessage($maximum);
            }

            if ($cartTotal < $minimum && $minimum != PHP_INT_MAX) {
                $eligibilityMsg = ' ' . Settings::getNonEligibilityMinAmountMessage($minimum);
            }

            $eligibilityMsg = Settings::getNonEligibilityMessage() . $eligibilityMsg;
        } else {
            $eligibilityMsg = Settings::getEligibilityMessage();
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
        $psVersion = "1.7";
        $refreshPrice = true;
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $psVersion = "1.6";
            $refreshPrice = false;
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $psVersion = "1.5";
            }
        }
        $this->context->smarty->assign([
            'eligibility_msg'   => $eligibilityMsg,
            'logo'              => $logo,
            'isExcluded'        => $isExcluded,
            'settings'          => [
                'merchantId'        => Settings::getMerchantId(),
                'apiMode'           => Settings::getActiveMode(),
                'amount'            => $cartTotal,
                'plans'             => Settings::activePlans(),
                'refreshPrice'      => $refreshPrice,
                'decimalSeparator'  => LocaleHelper::decimalSeparator(),
                'thousandSeparator' => LocaleHelper::thousandSeparator(),
                'psVersion'         => $psVersion,
            ],
        ]);

        return $this->module->display($this->module->file, 'displayShoppingCartFooter.tpl');
    }
}
