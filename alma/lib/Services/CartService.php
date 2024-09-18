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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\CartException;
use Alma\PrestaShop\Exceptions\ProductException;
use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\InsuranceProductHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Modules\OpartSaveCart\OpartSaveCartCartService;
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
     * @var ToolsFactory
     */
    protected $toolsFactory;
    /**
     * @var CartFactory
     */
    protected $cartFactory;
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @param CartProductRepository $cartProductRepository
     * @param ContextFactory $contextFactory
     * @param OpartSaveCartCartService $opartCartSaveService
     * @param InsuranceHelper $insuranceHelper
     * @param InsuranceProductHelper $insuranceProductHelper
     * @param ToolsFactory $toolsFactory
     * @param CartFactory $cartFactory
     * @param ProductHelper $productHelper
     */
    public function __construct(
        $cartProductRepository,
        $contextFactory,
        $opartCartSaveService,
        $insuranceHelper,
        $insuranceProductHelper,
        $toolsFactory,
        $cartFactory,
        $productHelper
    ) {
        $this->cartProductRepository = $cartProductRepository;
        $this->contextFactory = $contextFactory;
        $this->opartCartSaveService = $opartCartSaveService;
        $this->insuranceHelper = $insuranceHelper;
        $this->insuranceProductHelper = $insuranceProductHelper;
        $this->toolsFactory = $toolsFactory;
        $this->cartFactory = $cartFactory;
        $this->productHelper = $productHelper;
    }

    /**
     * @param \Cart $newCart
     * @param \Cart $currentCart
     *
     * @return void
     *
     * @throws AlmaException
     * @throws \PrestaShopException
     */
    public function duplicateAlmaInsuranceProductsIfNotExist($newCart, $currentCart)
    {
        // We check if alma insurance product exist because this function is executed for each product updated
        if (!$this->insuranceHelper->almaInsuranceProductsAlreadyExist($newCart)) {
            try {
                $this->insuranceProductHelper->duplicateAlmaInsuranceProducts($currentCart->id, $newCart->id);
            } catch (\PrestaShopDatabaseException $e) {
                $newCart->delete();
                // We throw an exception to prevent to buy insurance product without the possibility to subscribe
                throw new AlmaException('[Alma] Impossible to duplicate insurance product in fact error connect to database');
            }
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

    /**
     * @param int|false $productId
     * @param int $cartId
     *
     * @return bool|null
     *
     * @throws AlmaException
     * @throws CartException
     */
    public function deleteProductByCartId($productId, $cartId)
    {
        if (!$productId || !$cartId) {
            throw new CartException("[Alma] Product id and cart id are required. ProductId: {$productId}, cartId: {$cartId}");
        }

        $cart = $this->cartFactory->create($cartId);
        $languageId = $this->contextFactory->getContextLanguageId();

        try {
            $insuranceProductAttributes = $this->productHelper->getAttributeCombinationsByProductId($productId, $languageId);
        } catch (ProductException $e) {
            throw new CartException("[Alma] Cannot get Attribute combination of productId {$productId}, with languageId {$languageId} ");
        }

        try {
            foreach ($insuranceProductAttributes as $insuranceProductAttribute) {
                $cart->deleteProduct($productId, $insuranceProductAttribute['id_product_attribute']);
            }

            return true;
        } catch (\PrestaShopDatabaseException $e) {
            throw new CartException("[Alma] Error Database while deleting product from cart. ProductId: {$productId}, cartId: {$cartId}");
        } catch (\PrestaShopException $e) {
            throw new CartException("[Alma] Error while deleting product from cart. ProductId: {$productId}, cartId: {$cartId}");
        }
    }
}
