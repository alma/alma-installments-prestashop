<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HookHelper
 */
class HookHelper
{
    /**
     * @var array
     */
    protected $hooks;

    public function __construct()
    {
        $this->hooks = \Hook::getHooks();
    }

    /**
     * @var array
     */
    public static $almaHooks = [
        'moduleRoutes' => 'all',
        'actionAdminControllerInitBefore' => 'all',
        'header' => 'all',
        'displayHeader' => 'all',
        'displayBackOfficeHeader' => 'all',
        'displayShoppingCartFooter' => 'all',
        'actionOrderStatusPostUpdate' => 'all',
        'displayAdminAfterHeader' => 'all',
        'displayAdminOrderMain' => [
            'version' => '1.7.7.0',
            'operand' => '>=',
        ],
        'displayAdminOrder' => [
            'version' => '1.7.7.0',
            'operand' => '<',
        ],
        'paymentOptions' => [
            'version' => '1.7',
            'operand' => '>=',
        ],
        'paymentReturn' => [
            'version' => '1.7',
            'operand' => '>=',
        ],
        'displayPaymentReturn' => [
            'version' => '1.7',
            'operand' => '<',
        ],
        'displayPayment' => [
            'version' => '1.7',
            'operand' => '<',
        ],
        'displayPaymentEU' => [
            'version' => '1.7',
            'operand' => '<',
        ],
        'displayProductPriceBlock' => [
            'version' => '1.6',
            'operand' => '>=',
        ],
        'displayProductButtons' => [
            'version' => '1.7.6',
            'operand' => '<',
        ],
        'displayProductActions' => [
            'version' => '1.7.6',
            'operand' => '>=',
        ],
        'actionObjectProductInCartDeleteAfter' => [
            'version' => '1.7.1',
            'operand' => '>=',
        ],
        'actionCartSave' => 'all',
        'actionValidateOrder' => 'all',
        'displayCartExtraProductActions' => 'all',
        'termsAndConditions' => 'all',
        'actionOrderGridQueryBuilderModifier' => [
            'version' => '1.7.7',
            'operand' => '>=',
        ],
        'actionOrderGridDefinitionModifier' => [
            'version' => '1.7.7',
            'operand' => '>=',
        ],
        'displayAdminOrderTop' => [
            'version' => '1.7.7',
            'operand' => '>=',
        ],
        'actionGetProductPropertiesBefore' => [
            'version' => '1.7',
            'operand' => '>=',
        ],
    ];

    /**
     * Register the alma hooks
     *
     * @return array
     */
    public function almaRegisterHooks()
    {
        $hooksToRegister = [];

        foreach (static::$almaHooks as $hookName => $conditions) {
            if (!is_array($conditions)) {
                $hooksToRegister[] = $hookName;
                continue;
            }

            if (version_compare(_PS_VERSION_, $conditions['version'], $conditions['operand'])) {
                $hooksToRegister[] = $hookName;
            }
        }

        return $hooksToRegister;
    }
}
