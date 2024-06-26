<?php
/**
 * 2018-2023 Alma SAS.
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

use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Services\CartSaveService;
use Alma\PrestaShop\Services\InsuranceProductService;

class ActionCartSaveHookController extends FrontendHookController
{
    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var CartSaveService
     */
    protected $cartSaveService;

    public function canRun()
    {
        $isLive = SettingsHelper::getActiveMode() === ALMA_MODE_LIVE;

        // Front controllers can run if the module is properly configured ...
        return SettingsHelper::isFullyConfigured()
            // ... and the plugin is in LIVE mode, or the visitor is an admin
            && ($isLive || $this->loggedAsEmployee())
            && $this->insuranceHelper->isInsuranceActivated();
    }

    /**
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $this->insuranceProductService = new InsuranceProductService();
        $this->insuranceHelper = new InsuranceHelper();
        $this->productHelper = new ProductHelper();
        $this->context = \Context::getContext();
        $this->cartSaveService = new CartSaveService();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     *
     * @throws AlmaException
     */
    public function run($params)
    {
        $baseCart = $this->context->cart;
        /**
         * @var \Cart $newCart
         */
        $newCart = $params['cart'];

        // TODO : Need to optimise for more that the module opartSaveCart
        if (null === $baseCart->id || $baseCart->id != $newCart->id) {
            if (\Tools::getValue('action') !== 'shareCart') {
                $cartIdSaved = $this->cartSaveService->getIdCartSaved(\Tools::getValue('token'));
                if (!$cartIdSaved) {
                    return;
                }

                $baseCart = new \Cart($cartIdSaved);
            }

            if (!$this->insuranceHelper->checkInsuranceProductsExist($newCart)) {
                try {
                    $this->insuranceProductService->duplicateInsuranceProducts($baseCart, $newCart);
                } catch (\PrestaShopDatabaseException $e) {
                    Logger::instance()->error('[Alma] Error duplicating insurance products: ' . $e->getMessage());
                    $newCart->delete();
                    // We throw an exception to prevent to buy insurance product without the possibility to subscribe
                    throw new AlmaException('[Alma] Impossible to duplicate insurance product in fact error connect to database');
                }
            }

            return;
        }

        $this->handleAddingProductInsurance($params['cart']);
    }

    /**
     * @param \Cart $cart
     *
     * @return void
     */
    public function handleAddingProductInsurance($cart)
    {
        if (
            \Tools::getIsset('alma_id_insurance_contract')
            && 1 == \Tools::getValue('add')
            && 'update' == \Tools::getValue('action')
        ) {
            $this->insuranceProductService->handleAddingProductInsurance(
                \Tools::getValue('id_product'),
                \Tools::getValue('alma_id_insurance_contract'),
                \Tools::getValue('qty'),
                \Tools::getValue('id_customization'),
                $cart
            );
        }
    }
}
