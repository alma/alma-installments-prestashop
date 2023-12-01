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

namespace Alma\PrestaShop\Repositories;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CartProductRepository.
 *
 * Use for Cart Product
 */
class CartProductRepository
{

    /**
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $idCart
     * @param int $idAdressDelivery
     * @param int $idShop
     * @param int $quantity
     * @return int bool
     */
    public function add($idProduct, $idProductAttribute, $idCart, $idAdressDelivery, $idShop, $quantity)
    {
        return \Db::getInstance()->insert('cart_product', array(
            'id_product' => (int)$idProduct,
            'id_product_attribute' => (int)$idProductAttribute,
            'id_cart' => (int)$idCart,
            'id_address_delivery' => (int)$idAdressDelivery,
            'id_shop' => $idShop,
            'quantity' => (int)$quantity,
            'date_add' => date('Y-m-d H:i:s')
        ));

    }

    /**
     * @param int $quantity
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $idCart
     * @param int $isMultiAddressDelivery
     * @param int $idAddress
     * @return bool
     */
    public function update($quantity, $idProduct, $idProductAttribute, $idCart, $isMultiAddressDelivery, $idAddress)
    {
        return \Db::getInstance()->execute('
						UPDATE `' . _DB_PREFIX_ . 'cart_product`
						SET `quantity` = `quantity` ' . $quantity . ', `date_add` = NOW()
						WHERE `id_product` = ' . (int)$idProduct .
            (!empty($idProductAttribute) ? ' AND `id_product_attribute` = ' . (int)$idProductAttribute : '') . '
						AND `id_cart` = ' . (int)$idCart . (\Configuration::get('PS_ALLOW_MULTISHIPPING') && $isMultiAddressDelivery ? ' 
						AND `id_address_delivery` = ' . (int)$idAddress : '') . '
						LIMIT 1'
        );
    }

    /**
     * @param int $idProduct
     * @param int $idCart
     * @return false|string|null
     */
    public function hasProductInCart($idProduct, $idCart)
    {
       return  \Db::getInstance()->getValue('SELECT distinct(`id_product`)
            FROM `' . _DB_PREFIX_ . 'cart_product`
            where `id_cart` =' . (int)$idCart . '
            AND `id_product` = ' . (int)$idProduct );
    }
}
