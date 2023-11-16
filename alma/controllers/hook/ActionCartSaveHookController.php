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

use Alma\PrestaShop\Exceptions\InsuranceNotFoundException;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\AttributeRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

class ActionCartSaveHookController extends FrontendHookController
{
    /**
     * @var \Alma\PrestaShop\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * @var AttributeGroupRepository
     */
    protected $attributeGroupRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var LocaleHelper
     */
    private $localeHelper;

    public function __construct($module)
    {
        parent::__construct($module);

        $this->productRepository = new ProductRepository();
        $this->attributeGroupRepository = new AttributeGroupRepository();
        $this->attributeRepository = new AttributeRepository();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->localeHelper = new LocaleHelper();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     */
    public function run($params)
    {
        if (
            1 == \Tools::getValue('add')
            && 0 != \Tools::getValue('id_customization')
        ) {
            $lastProductAddInCart = $this->getLastProduct();

            $insuranceProductId = $this->productRepository->getProductIdByReference(
                ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
                $this->context->language->id
            );

            if (!$insuranceProductId) {
                // @todo la recréer ? envoyer un message
                throw new InsuranceNotFoundException();
            }

            if (
                $lastProductAddInCart['id_product'] != $insuranceProductId
            ) {
                $product = new \Product((int)\Tools::getValue('id_product'), false, null, $this->context->shop->id);

                $customizationFields = $product->getCustomizationFields(
                    $this->context->language->id,
                    $this->context->shop->id
                );

                $hasInsuranceCustom = false;

                if (is_array($customizationFields)) {
                    foreach ($customizationFields as $customizationField) {
                        if ($customizationField['name'] === ConstantsHelper::ALMA_INSURANCE_CUSTOMIZATION_NAME) {
                            $hasInsuranceCustom = true;
                            $almaInsuranceCustomizationFieldId = $customizationField['id_customization_field'];
                            break;
                        }
                    }
                }

                $nbProductsWithInsuranceInCart = $this->getNbProductWithInsuranceInCart()['nbProductsWithInsurance'];
                $nbInsuranceAssociated = $this->almaInsuranceProductRepository->getNbInsuranceByCart(
                    $this->context->shop->id,
                    $this->context->cart->id
                )['nbInsurance'];

                if (null == $nbProductsWithInsuranceInCart) {
                    $nbProductsWithInsuranceInCart = 0;
                }


                if (
                    $hasInsuranceCustom
            //        && $nbProductsWithInsuranceInCart == ($nbInsuranceAssociated + \Tools::getValue('qty'))
                ) {

                    $result = $this->getCustomizationValue(
                        \Tools::getValue('id_customization'),
                        $almaInsuranceCustomizationFieldId
                    );

                    $resultArray = explode('||', $result['value']);

                    foreach ($resultArray as $details) {
                        $detailsArray = explode(':', $details);
                        if ($detailsArray[0] == 'insuranceName') {
                            $insuranceName = $detailsArray[1];
                        }

                        if ($detailsArray[0] == 'insurancePrice') {
                            $insurancePrice = $detailsArray[1];
                        }
                    }

                    /**
                     * @var \ProductCore $defaultInsuranceProduct
                     */
                    $defaultInsuranceProduct = new \Product((int)$insuranceProductId);

                    $attributeGroupId = $this->attributeGroupRepository->getAttributeIdByName(
                        ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME,
                        $this->context->language->id
                    );

                    if (!$attributeGroupId) {
                        // @todo la recréer ? envoyer un message
                        throw new InsuranceNotFoundException();
                    }

                    $insuranceAttributeId = $this->attributeRepository->getAttributeIdByNameAndGroup(
                        $insuranceName,
                        $attributeGroupId,
                        $this->context->language->id
                    );

                    if (!$insuranceAttributeId) {
                        /**
                         * @var \AttributeCore $testNewAttribute
                         */
                        $insuranceAttribute = new \AttributeCore();

                        $insuranceAttribute->name = $this->localeHelper->createMultiLangField($insuranceName);
                        $insuranceAttribute->id_attribute_group = $attributeGroupId;
                        $insuranceAttribute->add();

                        $insuranceAttributeId = $insuranceAttribute->id;
                    }

                    // Check if the combination already exists

                    /**
                     * @var \CombinationCore $combinaison
                     */
                    $idInsuranceProductAttribute = \CombinationCore::getIdByReference($insuranceProductId, $insuranceName);

                    if (!$idInsuranceProductAttribute) {
                        $idInsuranceProductAttribute = $defaultInsuranceProduct->addCombinationEntity(
                            $insurancePrice,
                            $insurancePrice,
                            0,
                            1,
                            0,
                            1,
                            0,
                            $insuranceName,
                            0,
                            '',
                            0
                        );
                        $combinaison = new \CombinationCore((int)$idInsuranceProductAttribute);
                        $combinaison->setAttributes([$insuranceAttributeId]);
                    }
                    // ? id adress delivery
                    $sql = '
                    SELECT `quantity`
                    FROM `' . _DB_PREFIX_ . 'cart_product`
                    WHERE `id_cart` = ' . (int)$this->context->cart->id . '
                    and `id_product` = ' . (int)$insuranceProductId . '
                    and `id_product_attribute` = ' . (int)$idInsuranceProductAttribute;

                    $resultQuantity = \Db::getInstance()->getRow($sql);

                    if (!$resultQuantity) {
                        $sql = '
                    insert into `' . _DB_PREFIX_ . 'cart_product` 
                     (`id_cart`, `id_product`,`id_product_attribute`, `id_customization`, `quantity`, `id_address_delivery`, `date_add`)
                    values ( 
                     ' . (int)$this->context->cart->id . ' ,
                     ' . (int)$insuranceProductId . ' ,
                     ' . (int)$idInsuranceProductAttribute . ',
                     0,
                      ' . (int) \Tools::getValue('qty'). ',
                           ' . (int)$this->context->cart->id_address_delivery . ',
                           NOW()
                      )';

                        \Db::getInstance()->execute($sql);
                    } else {
                        $quantity = $resultQuantity['quantity'] + \Tools::getValue('qty');

                        $sql = '
                        update `' . _DB_PREFIX_ . 'cart_product` 
                        SET quantity =   ' . (int) $quantity. '
                        WHERE
                        `id_cart` =     ' . (int)$this->context->cart->id . ' 
                        AND `id_product` =      ' . (int)$insuranceProductId . ' 
                        AND `id_product_attribute` =  ' . (int)$idInsuranceProductAttribute . ' 
                        AND `id_customization` = 0
                        AND `id_address_delivery` = ' . (int)$this->context->cart->id_address_delivery;

                         \Db::getInstance()->execute($sql);
                    }

                    \StockAvailable::setQuantity(
                        $defaultInsuranceProduct->id,
                      $idInsuranceProductAttribute,
                      \Tools::getValue('qty'),
                      $this->context->shop->id
                      );

       /**             $this->context->cart->updateQty(
                        \Tools::getValue('qty'),
                        $defaultInsuranceProduct->id,
                        $idInsuranceProductAttribute
                    );**/


                 /**   $this->updateCustomizationValue(
                        \Tools::getValue('id_customization'),
                        $almaInsuranceCustomizationFieldId,
                        'TOUHOU'
                    );**/

                    $this->almaInsuranceProductRepository->add(
                        $this->context->cart->id,
                        \Tools::getValue('id_product'),
                        $this->context->shop->id,
                        \Tools::getValue('id_product_attribute'),
                        \Tools::getValue('id_customization'),
                        $insuranceProductId,
                        $insuranceAttributeId,
                        $insurancePrice
                    );

                }
            }
        }
        // @todo suppression + decrease
    }

    public function updateCustomizationValue($idCustomization, $index, $value)
    {
        $sql = '
            UPDATE `' . _DB_PREFIX_ . 'customized_data`
            SET `value` = " ' . $value . '" 
            WHERE `id_customization` = ' . (int)$idCustomization . '
            AND `type` = 1 
            AND `index` = ' . (int)$index;

        return \Db::getInstance()->execute($sql);
    }

    public function getCustomizationValue($idCustomization, $index)
    {
        $sql = '
            SELECT `id_customization`, `type`, `index`, `value`
            FROM `' . _DB_PREFIX_ . 'customized_data`
            WHERE `id_customization` = ' . (int)$idCustomization . '
            AND `type` = 1 
            AND `index` = ' . (int)$index;

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Get last Product in Cart.
     *
     * @return bool|mixed Database result
     */
    public function getLastProduct()
    {
        $sql = '
            SELECT `id_product`, `id_product_attribute`, id_shop, id_customization
            FROM `' . _DB_PREFIX_ . 'cart_product`
            WHERE `id_cart` = ' . (int)$this->context->cart->id . '
            ORDER BY `date_add` DESC';

        return \Db::getInstance()->getRow($sql);
    }

    public function getNbProductWithInsuranceInCart()
    {
        $sql = '
        SELECT sum(cp.`quantity`) as nbProductsWithInsurance
            from `' . _DB_PREFIX_ . 'cart_product` cp
            join `' . _DB_PREFIX_ . 'cart` ca on ca.`id_cart` = cp.`id_cart`
            left join `' . _DB_PREFIX_ . 'customization` c
                on c.`id_customization` = cp.`id_customization`
                and c.`id_product_attribute` = cp.`id_product_attribute`
                and c.`id_product`  = cp.`id_product`
                and c.`id_cart` = ca.`id_cart`
            join `' . _DB_PREFIX_ . 'customization_field` cf on cf.`id_product` = cp.`id_product`
            join `' . _DB_PREFIX_ . 'customization_field_lang` cfl on cf.`id_customization_field` = cfl.`id_customization_field`
            join `' . _DB_PREFIX_ . 'customized_data` cd
                on cd.`id_customization` = cp.`id_customization`
                and cd.`type` = 1
                and cd.`index` = cf.`id_customization_field`
            where cp.`id_cart` = ' . (int)$this->context->cart->id . '
            and cp.`id_shop` = ' . (int)$this->context->shop->id . '
            and cp.`id_customization` != 0
            and ca.`id_shop` = ' . (int)$this->context->shop->id . '
            and ca.`id_lang` = ' . (int)$this->context->language->id . '
            and cf.`type` = 1
            and cf.`is_deleted` = 0
            and cfl.`id_shop` = ' . (int)$this->context->shop->id . '
            and cfl.`id_lang` = ' . (int)$this->context->language->id . '
            and cfl.`name` = "' . ConstantsHelper::ALMA_INSURANCE_CUSTOMIZATION_NAME . '"
        ';

        return \Db::getInstance()->getRow($sql);
    }

}
