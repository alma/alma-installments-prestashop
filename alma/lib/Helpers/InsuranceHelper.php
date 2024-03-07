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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\CartProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    public function __construct(
        $cartProductRepository = null,
        $productRepository = null,
        $insuranceProductRepository = null,
        $context = null
    ) {
        if (!$cartProductRepository) {
            $cartProductRepository = new CartProductRepository();
        }
        if (!$productRepository) {
            $productRepository = new ProductRepository();
        }
        if (!$insuranceProductRepository) {
            $insuranceProductRepository = new AlmaInsuranceProductRepository();
        }
        if (!$context) {
            $context = \Context::getContext();
        }
        $this->cartProductRepository = $cartProductRepository;
        $this->productRepository = $productRepository;
        $this->insuranceProductRepository = $insuranceProductRepository;
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function isInsuranceAllowedInProductPage()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7', '>=')
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false)
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_ALLOW_INSURANCE, false)
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false);
    }

    /**
     * @return bool
     */
    public function isInsuranceActivated()
    {
        return (bool) version_compare(_PS_VERSION_, '1.7', '>=')
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_ALLOW_INSURANCE, false)
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false);
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
}
