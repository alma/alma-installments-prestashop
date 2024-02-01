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

use Alma\PrestaShop\Helpers\SettingsHelper;

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
    /**
     * @param int $idCart
     * @param int $idProduct
     * @param int $idShop
     * @param int $idProductAttribute
     * @param int $idCustomization
     * @param int $idProductInsurance
     * @param int $idProductAttributeInsurance
     * @param float $assurancePrice
     * @param int $idAddressDelivery
     * @param array $insuranceContractInfos
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    public function add(
        $idCart,
        $idProduct,
        $idShop,
        $idProductAttribute,
        $idCustomization,
        $idProductInsurance,
        $idProductAttributeInsurance,
        $assurancePrice,
        $idAddressDelivery,
        $insuranceContractInfos
    )
    {
        if (!\Db::getInstance()->insert('alma_insurance_product', [
            'id_cart' => $idCart,
            'id_product' => $idProduct,
            'id_shop' => $idShop,
            'id_product_attribute' => $idProductAttribute,
            'id_customization' => $idCustomization,
            'id_product_insurance' => $idProductInsurance,
            'id_product_attribute_insurance' => $idProductAttributeInsurance,
            'price' => $assurancePrice,
            'id_address_delivery' => $idAddressDelivery,
            'insurance_contract_id' => $insuranceContractInfos['insurance_contract_id'],
            'cms_reference' => $insuranceContractInfos['cms_reference'],
            'product_price' => $insuranceContractInfos['product_price'],
            'mode' => SettingsHelper::getActiveMode()
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
     * @throws \PrestaShopDatabaseException
     */
    public function getIdsByCartIdAndShop($cartId, $shopId)
    {
        $sql = '
            SELECT `id_alma_insurance_product` as id
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` aip
            WHERE aip.`id_cart` = ' . (int)$cartId . '
            AND aip.`id_shop` = ' . (int)$shopId;

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * @param \ProductCore $product
     * @param int $cartId
     * @param int $shopId
     * @return mixed
     * @throws \PrestaShopDatabaseException
     */
    public function getIdsByCartIdAndShopAndProduct($product, $cartId, $shopId)
    {
        $sql = '
            SELECT `id_alma_insurance_product`,
                   `id_product_insurance`,
                   `id_product_attribute_insurance`,
                   `price` 
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` aip
            WHERE aip.`id_cart` = ' . (int)$cartId . '
            AND aip.`id_product` = ' . (int)$product->id . '
            AND aip.`id_product_attribute` = ' . (int)$product->id_product_attribute . '
            AND aip.`id_customization` = ' . (int)$product->id_customization . ' 
            AND aip.`id_shop` = ' . (int)$shopId;

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById($id)
    {
        $sql = '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` aip
            WHERE aip.`id_alma_insurance_product` = ' . (int)$id;

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id)
    {
        \Db::getInstance()->execute('
             DELETE 
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` 
            WHERE `id_alma_insurance_product` = ' . (int)$id
        );
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
                SET `id_order` =' . (int)$orderId . ' 
                WHERE `id_alma_insurance_product` IN (' . implode(',', $idsToUpdate) . ')'
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'alma_insurance_product` (
            `id_alma_insurance_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) unsigned NOT NULL,
            `id_product` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL DEFAULT 1,
            `id_product_attribute` int(10) unsigned NOT NULL DEFAULT 0,
            `id_customization` int(10) unsigned NOT NULL DEFAULT 0,
            `id_product_insurance` int(10) unsigned NOT NULL,
            `id_product_attribute_insurance` int(10) unsigned NOT NULL,
            `id_address_delivery` int(10) unsigned NOT NULL,
            `id_order` int(10) unsigned NULL,
            `price` decimal(20,6) NOT NULL DEFAULT 0.000000,
            `insurance_contract_id` varchar(255) NULL,
            `cms_reference` varchar(255) NULL,
            `product_price` int(10) unsigned NULL,
            `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `subscription_id` varchar(255) null,
            `subscription_amount` int(10) unsigned NULL,
            `subscription_broker_id` varchar(255) null,
            `state` varchar(255) null,
            `date_of_cancelation` datetime null,
            `reason_of_cancelation` text null,   
            `is_refunded` boolean default 0 null,
            `date_of_refund` datetime null,
            `date_of_cancelation_request` datetime null,
            `mode` varchar(255) not NULL,
            PRIMARY KEY (`id_alma_insurance_product`) ,
            index `ps_alma_insurance_product_cart_shop` (`id_cart`, `id_shop`),
            index `ps_alma_insurance_product`  (`id_product`, `id_shop`, `id_product_attribute`, `id_customization`, `id_cart`) ,
            index `ps_broker_id`  (`subscription_broker_id`) ,
            constraint ps_alma_insurance_product_pk  unique (`subscription_id`) ,
            constraint ps_alma_insurance_broker_pk  unique (`subscription_broker_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        return \Db::getInstance()->execute($sql);
    }

    /**
     * @param int $idCart
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $customizationId
     * @param int $idShop
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getAllByProduct($idCart, $idProduct, $idProductAttribute, $customizationId, $idShop)
    {
        $sql = '
            SELECT `id_alma_insurance_product`,
                   `id_product_insurance`,
                   `id_product_attribute_insurance`
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` aip
            WHERE aip.`id_cart` = ' . (int)$idCart . '
            AND aip.`id_product` = ' . (int)$idProduct. '
            AND aip.`id_product_attribute` = ' . (int)$idProductAttribute . '
            AND aip.`id_customization` = ' . (int)$customizationId. '
            AND aip.`id_shop` = ' . (int) $idShop;

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $idCart
     * @param int $idShop
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getContractsInfosByIdCartAndIdShop($idCart, $idShop)
    {
        $sql = '
            SELECT `id_alma_insurance_product`, `insurance_contract_id`, `cms_reference`, `product_price` 
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` aip
            WHERE aip.`id_cart` = ' . (int)$idCart . '
            AND aip.`id_shop` = ' . (int)$idShop;

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $orderId
     * @param int $shopId
     * @param string $contractId
     * @param string $cmsReference
     * @return array|null
     */
    public function findSubscriptionToActivate($orderId, $shopId, $contractId, $cmsReference)
    {
        $sql = '
            SELECT `id_alma_insurance_product`
            FROM `' . _DB_PREFIX_ . 'alma_insurance_product` 
            WHERE `id_order` = ' . (int)$orderId. '
            AND `insurance_contract_id` = "' . $contractId. '" 
            AND `cms_reference` = "' . $cmsReference. '" 
            AND `state` is NULL 
            AND `id_shop` = ' . (int)$shopId;

        return \Db::getInstance()->getRow($sql);
    }
}
