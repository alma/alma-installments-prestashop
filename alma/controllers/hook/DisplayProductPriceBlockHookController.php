<?php
/**
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Utils\LocaleHelper;
use Alma\PrestaShop\Utils\Settings;
use Alma\PrestaShop\Utils\SettingsCustomFields;
use Product;
use Tools;

class DisplayProductPriceBlockHookController extends FrontendHookController
{
    public function canRun()
    {
        return parent::canRun() &&
            Tools::strtolower($this->currentControllerName()) == 'product' &&
            Settings::showProductEligibility() &&
            Settings::getMerchantId() != null;
    }

    public function run($params)
    {
        if (array_key_exists('type', $params)) {
            if (version_compare(_PS_VERSION_, '1.7', '>')) {
                $skip = $params['type'] === 'price' || (!in_array($params['type'], ['price', 'after_price']));
            } elseif (version_compare(_PS_VERSION_, '1.6', '>')) {
                $skip = $params['type'] !== 'weight';
            } else {
                $skip = !in_array($params['type'], ['price', 'after_price']);
            }

            if ($skip) {
                return null;
            }
        }

        /* @var Product $product */
        if (isset($params['product']) && $params['product'] instanceof Product) {
            $product = $params['product'];
            $price = almaPriceToCents($product->getPrice(true));
            $productId = $product->id;

            // Since we don't have access to the combination ID nor the wanted quantity, we should reload things from
            // the frontend to make sure we're displaying something relevant
            $refreshPrice = true;
        } else {
            $productParams = isset($params['product']) ? $params['product'] : [];

            $productId = isset($productParams['id_product'])
                ? $productParams['id_product']
                : Tools::getValue('id_product');

            $productAttributeId = isset($productParams['id_product_attribute'])
                ? $productParams['id_product_attribute']
                : null;

            if (!isset($productParams['quantity_wanted']) && !isset($productParams['minimal_quantity'])) {
                $quantity = 1;
            } elseif (!isset($productParams['quantity_wanted'])) {
                $quantity = (int) $productParams['minimal_quantity'];
            } elseif (!isset($productParams['minimal_quantity'])) {
                $quantity = (int) $productParams['quantity_wanted'];
            } else {
                $quantity = max((int) $productParams['minimal_quantity'], (int) $productParams['quantity_wanted']);
            }

            $price = almaPriceToCents(
                Product::getPriceStatic(
                    $productId,
                    true,
                    $productAttributeId,
                    6,
                    null,
                    false,
                    true,
                    $quantity
                )
            );

            // Being able to use `quantity_wanted` here means we don't have to reload price on the front-end
            $price *= $quantity;
            $refreshPrice = $productAttributeId === null;
        }

        if (Tools::getValue('id_product') != $productId) {
            return null;
        }

        $psVersion = 'ps15';
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $psVersion = 'ps17';
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $psVersion = 'ps16';
        }

        $activePlans = Settings::activePlans();

        $locale = Settings::localeByIdLangForWidget($this->context->language->id);

        if (!$activePlans) {
            return;
        }

        $isEligible = true;

        if (!Settings::showProductWidgetIfNotEligible()) {
            $feePlans = json_decode(Settings::getFeePlans());

            $isEligible = false;
            foreach ($feePlans as $feePlan) {
                if (1 == $feePlan->enabled) {
                    if ($price >= $feePlan->min && $price <= $feePlan->max) {
                        $isEligible = true;
                        break;
                    }
                }
            }
        }
        if (!Settings::showCategoriesWidgetIfNotEligible() && Settings::isProductExcluded($productId)) {
            $isEligible = false;
        }
        if ($isEligible) {
            $this->context->smarty->assign([
            'productId' => $productId,
            'psVersion' => $psVersion,
            'logo' => almaSvgDataUrl(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/logo_alma.svg'),
            'isExcluded' => Settings::isProductExcluded($productId),
            'exclusionMsg' => SettingsCustomFields::getNonEligibleCategoriesMessageByLang($this->context->language->id),
            'settings' => [
                'merchantId' => Settings::getMerchantId(),
                'apiMode' => Settings::getActiveMode(),
                'amount' => $price,
                'plans' => $activePlans,
                'refreshPrice' => $refreshPrice,
                'decimalSeparator' => LocaleHelper::decimalSeparator(),
                'thousandSeparator' => LocaleHelper::thousandSeparator(),
                'showIfNotEligible' => Settings::showProductWidgetIfNotEligible(),
                'locale' => $locale,
                ],
            'widgetQuerySelectors' => json_encode([
                'price' => Settings::getProductPriceQuerySelector(),
                'attrSelect' => Settings::getProductAttrQuerySelector(),
                'attrRadio' => Settings::getProductAttrRadioQuerySelector(),
                'colorPick' => Settings::getProductColorPickQuerySelector(),
                'quantity' => Settings::getProductQuantityQuerySelector(),
                'isCustom' => Settings::isWidgetCustomPosition(),
                'position' => Settings::getProductWidgetPositionQuerySelector(),
                ]),
            ]);

            return $this->module->display($this->module->file, 'displayProductPriceBlock.tpl');
        }
    }
}
