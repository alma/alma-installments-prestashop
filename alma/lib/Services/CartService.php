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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Modules\OpartSaveCart\CartService as OpartSaveCartCartService;
use Alma\PrestaShop\Repositories\CartProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartService
{
    /**
     * @var CartProductRepository
     */
    protected $cartProductRepository;

    /**
     * @var OpartSaveCartCartService
     */
    protected $opartCartSaveService;
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var InsuranceProductHelper
     */
    protected $insuranceProductHelper;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @param CartProductRepository $cartProductRepository
     * @param ContextFactory $contextFactory
     * @param OpartSaveCartCartService $opartCartSaveService
     * @param InsuranceHelper $insuranceHelper
     * @param InsuranceProductHelper $insuranceProductHelper
     */
    public function __construct($cartProductRepository, $contextFactory, $opartCartSaveService, $insuranceHelper, $insuranceProductHelper)
    {
        $this->cartProductRepository = $cartProductRepository;
        $this->contextFactory = $contextFactory;
        $this->opartCartSaveService = $opartCartSaveService;
        $this->insuranceHelper = $insuranceHelper;
        $this->insuranceProductHelper = $insuranceProductHelper;
    }

    /**
     * @param \Cart $newCart
     *
     * @return void
     */
    public function duplicateCart($newCart)
    {
        $currentCart = $this->contextFactory->getContextCart();

        if (
            $currentCart
            && null === $currentCart->id
        ) {
            $currentCart = $this->opartCartSaveService->getCartSaved();
        }

        if (
            $currentCart
            && $currentCart->id != $newCart->id
        ) {
            $this->duplicateInsuranceProductsInDB($newCart, $currentCart);
        }
    }

    /***
     * @param \Cart $newCart
     * @param \Cart $currentCart
     * @return void
     */
    public function duplicateInsuranceProductsInDB($newCart, $currentCart)
    {
        if (!$this->insuranceHelper->checkInsuranceProductsExist($newCart)) {
            $this->insuranceProductHelper->duplicateInsuranceProducts($currentCart, $newCart);
        }
    }

    /**
     * Update product quantity
     *
     * @param int $quantity Quantity to add (or substract)
     * @param int $idProduct Product ID
     * @param int $idProductAttribute Attribute ID if needed
     * @param \Cart|null $cart Object Cart
     * @param bool $idCustomization Customization ID if needed
     * @param string $operator Indicate if quantity must be increased or decreased
     * @param int $idAddressDelivery Address Delivery ID if needed
     * @param null $shop
     *
     * @return bool|int|void|null
     *
     * @throws AlmaException
     */
    public function updateQty(
        $quantity,
        $idProduct,
        $idProductAttribute = null,
        $cart = null,
        $idCustomization = false,
        $operator = 'up',
        $idAddressDelivery = 0,
        $shop = null
    ) {
        /**
         * @var \ContextCore $context
         */
        $context = $this->contextFactory->getContext();

        if (!$shop) {
            $shop = $context->shop;
        }

        if (null !== $context->cart) {
            $cart = $context->cart;
        }

        if ($context->customer->id) {
            if (
                $idAddressDelivery == 0
                && (int) $cart->id_address_delivery
            ) { // The $idAddressDelivery is null, use the cart delivery address
                $idAddressDelivery = $cart->id_address_delivery;
            } elseif ($idAddressDelivery == 0) { // The $idAddressDelivery is null, get the default customer address
                $idAddressDelivery = (int) \Address::getFirstCustomerAddressId((int) $context->customer->id);
            } elseif (!\Customer::customerHasAddress($context->customer->id, $idAddressDelivery)) { // The $idAddressDelivery must be linked with customer
                $idAddressDelivery = 0;
            } else {
                $idAddressDelivery = 0;
            }
        }

        $quantity = (int) $quantity;
        $idProduct = (int) $idProduct;
        $idProductAttribute = (int) $idProductAttribute;
        $product = new \Product($idProduct, false, \Configuration::get('PS_LANG_DEFAULT'), $shop->id);

        if ($idProductAttribute) {
            $combination = new \Combination((int) $idProductAttribute);
            if ($combination->id_product != $idProduct) {
                return false;
            }
        }

        if (!\Validate::isLoadedObject($product)) {
            throw new AlmaException(sprintf('The product does not exists %s', $idProduct));
        }

        if ((int) $quantity <= 0) {
            return $cart->deleteProduct($idProduct, $idProductAttribute, (int) $idCustomization, 0);
        }
        /* Check if the product is already in the cart */
        $resultContainsProduct = $cart->containsProduct($idProduct, $idProductAttribute, (int) $idCustomization, (int) $idAddressDelivery);

        /* Update quantity if product already exist */
        if ($resultContainsProduct) {
            switch ($operator) {
                case 'up':
                    $newQty = (int) $resultContainsProduct['quantity'] + (int) $quantity;
                    $qty = '+ ' . (int) $quantity;
                    break;
                case 'down':
                    $qty = '- ' . (int) $quantity;
                    $newQty = (int) $resultContainsProduct['quantity'] - (int) $quantity;
                    if ($newQty < 0) {
                        throw new AlmaException(sprintf('Quantity issue , Product %s, Qty %s', $idProduct, $newQty));
                    }

                    break;
                default:
                    throw new AlmaException(sprintf('Unknown operator %s', $operator));
            }

            /* Delete product from cart */
            if ($newQty <= 0) {
                return $cart->deleteProduct((int) $idProduct, (int) $idProductAttribute, (int) $idCustomization, 0);
            }

            return $this->cartProductRepository->update(
                $qty,
                $idProduct,
                $idProductAttribute,
                $cart->id,
                $cart->isMultiAddressDelivery(),
                $idAddressDelivery
            );
        }

        /* Add product to the cart */
        if ($operator == 'up') {
            return $this->cartProductRepository->add(
                $idProduct,
                $idProductAttribute,
                $cart->id,
                $idAddressDelivery,
                $shop->id,
                $quantity
            );
        }
    }
}
