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

use Alma\PrestaShop\Helpers\ConstantsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AlmaInsuranceProductRepository.
 *
 * Use for Product
 */
class AlmaInsuranceProductRepository
{
    public function add($idCart, $idProduct, $idShop, $idProductAttribute, $idCustomization, $idProductInsurance, $isProductAttributeInsurance, $assurancePrice)
    {
        if (!\Db::getInstance()->insert('alma_insurance_product', [
            'id_cart' => $idCart,
            'id_product' => $idProduct,
            'id_shop' => $idShop,
            'id_product_attribute' => $idProductAttribute,
            'id_customization' => $idCustomization,
            'id_product_insurance' => $idProductInsurance,
            'id_product_attribute_insurance' => $isProductAttributeInsurance,
            'price' => $assurancePrice,
        ])) {
            return false;
        }

        return true;
    }

    /**
     * @param int $cartId
     * @param int $shopId
     *
     * @return mixed
     */
    public function getIdsByCartIdAndShop($cartId, $shopId)
    {
        $sql = '
            SELECT `id_alma_insurance_product` as id
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` aip
            WHERE aip.`id_cart` = ' . (int) $cartId . '
            AND aip.`id_shop` = ' . (int) $shopId;

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $orderId
     * @param array $idsToUpdate
     *
     * @return bool
     */
    public function updateAssociationsOrderId($orderId, $idsToUpdate)
    {
        if (
            !\Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'alma_insurance_product` 
                SET `id_order` =' . (int) $orderId . ' 
                WHERE `id_alma_insurance_product` IN (' . implode(',', $idsToUpdate) . ')'
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param int $idShop
     * @param int $idCart
     * @return array|bool|object|null
     */
    public function getNbInsuranceByCart($idShop, $idCart)
    {
        $sql = '
            select count(`id_alma_insurance_product`) as nbInsurance 
            from `' . _DB_PREFIX_ . 'alma_insurance_product`
            where id_cart = ' . (int) $idCart. '
            and id_shop = ' . (int)$idShop . '
        ';

        return  \Db::getInstance()->getRow($sql);
    }
}
