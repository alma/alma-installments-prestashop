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

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @deprecated We will remove insurance
 */
class InsuranceHelper
{
    const ALMA_INSURANCE_STATUS_FAILED = 'failed';
    const ALMA_INSURANCE_STATUS_CANCELED = 'canceled';
    const ALMA_INSURANCE_STATUS_PENDING_CANCELLATION = 'pending_cancellation';

    /**
     * @var CartProductRepository
     */
    public $cartProductRepository;

    /**
     * @var ProductRepository
     */
    public $productRepository;

    /**
     * @var AlmaInsuranceProductRepository
     */
    public $insuranceProductRepository;
    /**
     * @var \Context|null
     */
    protected $context;
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /***
     * @param CartProductRepository $cartProductRepository
     * @param ProductRepository $productRepository
     * @param AlmaInsuranceProductRepository $insuranceProductRepository
     * @param ContextFactory $contextFactory
     * @param ToolsHelper $toolsHelper
     * @param SettingsHelper $settingsHelper
     */
    public function __construct(
        $cartProductRepository,
        $productRepository,
        $insuranceProductRepository,
        $contextFactory,
        $toolsHelper,
        $settingsHelper
    ) {
        $this->cartProductRepository = $cartProductRepository;
        $this->productRepository = $productRepository;
        $this->insuranceProductRepository = $insuranceProductRepository;
        $this->context = $contextFactory->getContext();
        $this->settingsHelper = $settingsHelper;
        $this->toolsHelper = $toolsHelper;
    }

    /**
     * @return bool
     */
    public function isInsuranceAllowedInProductPage()
    {
        return (bool) $this->toolsHelper->psVersionCompare('1.7', '>=')
            && (bool) (int) $this->settingsHelper->getKey(ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false)
            && $this->isInsuranceActivated();
    }

    /**
     * @return bool
     */
    public function isInsuranceAllowedInCartPage()
    {
        return (bool) $this->toolsHelper->psVersionCompare('1.7', '>=')
            && (bool) (int) $this->settingsHelper->getKey(ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, false)
            && $this->isInsuranceActivated();
    }

    /**
     * @return bool
     */
    public function isInsuranceActivated()
    {
        return (bool) $this->toolsHelper->psVersionCompare('1.7', '>=')
            && (bool) (int) $this->settingsHelper->getKey(ConstantsHelper::ALMA_ALLOW_INSURANCE, false)
            && (bool) (int) $this->settingsHelper->getKey(ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false);
    }

    /**
     * @param \OrderCore $order
     *
     * @return bool
     */
    public function canInsuranceSubscriptionBeTriggered($order)
    {
        $rowTriggered = $this->insuranceProductRepository->hasOrderBeenTriggered($order);

        if ($rowTriggered) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function hasInsuranceInCart()
    {
        $idInsuranceProduct = $this->productRepository->getProductIdByReference(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE);

        if (!$idInsuranceProduct) {
            return false;
        }

        $idProduct = $this->cartProductRepository->hasProductInCart($idInsuranceProduct, $this->context->cart->id);

        return (bool) $idProduct;
    }

    /**
     * @param \OrderCore $order
     *
     * @return bool
     */
    public function canRefundOrder($order)
    {
        $result = $this->insuranceProductRepository->canRefundOrder($order->id, $order->id_shop);

        if ($result['nbNotCancelled'] > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $productId
     * @param $productAttributeId
     *
     * @return string|null
     */
    public function createCmsReference($productId, $productAttributeId)
    {
        if ($productId !== null) {
            if ((int) $productAttributeId <= 0) {
                return (string) $productId;
            }

            return $productId . '-' . $productAttributeId;
        }

        LoggerFactory::instance()->error('[Alma] Impossible to create cms reference, productId is null');

        return null;
    }

    /**
     * Check if there is an insurance product in the cart
     *
     * @param \Cart $cart
     *
     * @return bool
     */
    public function almaInsuranceProductsAlreadyExist($cart)
    {
        if ($this->insuranceProductRepository->hasInsuranceForCartIdAndShop($cart->id, $cart->id_shop)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getInsuranceQuantity()
    {
        $quantity = \Tools::getValue('qty');

        if (\Tools::getIsset('alma_quantity_insurance')) {
            $quantity = \Tools::getValue('alma_quantity_insurance');
        }

        return $quantity;
    }
}
