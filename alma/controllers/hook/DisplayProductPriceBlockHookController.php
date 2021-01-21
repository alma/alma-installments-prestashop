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

use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Utils\LocaleHelper;
use Alma\PrestaShop\Utils\Settings;
use Product;

final class DisplayProductPriceBlockHookController extends FrontendHookController
{
    public function canRun()
    {
        return parent::canRun() &&
            $this->context->controller->php_self == 'product' &&
            Settings::showProductEligibility() &&
            Settings::getMerchantId() != null;
    }

    public function run($params)
    {
        if (array_key_exists('type', $params)) {
            $skip = (version_compare(_PS_VERSION_, '1.6.0', '>') && $params['type'] === 'price') ||
                (!in_array($params['type'], ['price', 'after_price']));
            if ($skip) {
                return null;
            }
        }

        /* @var Product $product */
        if ($params['product'] instanceof Product) {
            $product = $params['product'];
            $price = almaPriceToCents($product->getPrice(true));
            $productId = $product->id;

            // Since we don't have access to the combination ID nor the wanted quantity, we should reload things from
            // the frontend to make sure we're displaying something relevant
            $refreshPrice = true;
        } else {
            $productParams = $params['product'];
            $productId = $productParams['id_product'];

            $quantity = max((int) $productParams['minimal_quantity'], (int) $productParams['quantity_wanted']);
            $price = almaPriceToCents(
                Product::getPriceStatic(
                    $productId,
                    true,
                    $productParams['id_product_attribute'],
                    6,
                    null,
                    false,
                    true,
                    $quantity
                )
            );

            // Being able to use `quantity_wanted` here means we don't have to reload price on the front-end
            $price *= $quantity;
            $refreshPrice = false;
        }

        $psVersion = 'ps15';
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $psVersion = 'ps17';
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $psVersion = 'ps16';
        }

        $this->context->smarty->assign(
            [
                'productId' => $productId,
                'psVersion' => $psVersion,
                'logo' => almaSvgDataUrl(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/logo_alma.svg'),
                'isExcluded' => Settings::isProductExcluded($productId),
                'exclusionMsg' => Settings::getNonEligibleCategoriesMessage(),
                'settings' => [
                    'merchantId' => Settings::getMerchantId(),
                    'apiMode' => Settings::getActiveMode(),
                    'amount' => $price,
                    'plans' => Settings::activePlans(),
                    'refreshPrice' => $refreshPrice,
                    'decimalSeparator' => LocaleHelper::decimalSeparator(),
                    'thousandSeparator' => LocaleHelper::thousandSeparator(),
                ],
            ]
        );

        return $this->module->display($this->module->file, 'displayProductPriceBlock.tpl');
    }
}
