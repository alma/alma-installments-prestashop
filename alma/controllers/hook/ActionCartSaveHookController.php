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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Builders\Helpers\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Modules\OpartSaveCart\OpartSaveCartCartServiceBuilder;
use Alma\PrestaShop\Builders\Services\CartServiceBuilder;
use Alma\PrestaShop\Builders\Services\InsuranceProductServiceBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Modules\OpartSaveCart\OpartSaveCartCartService;
use Alma\PrestaShop\Services\CartService;
use Alma\PrestaShop\Services\InsuranceProductService;

class ActionCartSaveHookController extends FrontendHookController
{
    /**
     * @var \Context
     */
    protected $contextCart;

    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var ToolsFactory
     */
    protected $toolsFactory;
    /**
     * @var OpartSaveCartCartService
     */
    protected $opartCartSaveService;

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

        $contextFactory = new ContextFactory();
        $this->contextCart = $contextFactory->getContextCart();
        $insuranceProductServiceBuilder = new InsuranceProductServiceBuilder();
        $this->insuranceProductService = $insuranceProductServiceBuilder->getInstance();
        $this->toolsFactory = new ToolsFactory();
        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();
        $cartServiceBuilder = new CartServiceBuilder();
        $this->cartService = $cartServiceBuilder->getInstance();
        $opartCartSaveServiceBuilder = new OpartSaveCartCartServiceBuilder();
        $this->opartCartSaveService = $opartCartSaveServiceBuilder->getInstance();
        $this->logger = new Logger();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        $idProduct = $this->toolsFactory->getValue('id_product');
        $insuranceContractId = $this->toolsFactory->getValue('alma_id_insurance_contract');
        $quantity = $this->insuranceHelper->getInsuranceQuantity();
        $idCustomization = $this->toolsFactory->getValue('id_customization');
        $baseCart = $this->contextCart;
        $newCart = $params['cart'];

        try {
            if (
                $baseCart
                && (null === $baseCart->id || $baseCart->id != $newCart->id)
            ) {
                if ($this->toolsFactory->getValue('action') !== 'shareCart') {
                    $baseCart = $this->opartCartSaveService->getCartSaved();
                }

                $this->cartService->duplicateAlmaInsuranceProductsIfNotExist($newCart, $baseCart);
            }

            if ($this->insuranceProductService->canHandleAddingProductInsuranceOnce()) {
                $this->insuranceProductService->addInsuranceProductInPsCart(
                    $idProduct,
                    $insuranceContractId,
                    $quantity,
                    $idCustomization,
                    $params['cart']
                );
            }
        } catch (AlmaException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
