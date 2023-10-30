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

use Alma\PrestaShop\Exceptions\InsuranceNotFoundException;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\AttributeRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

class InsuranceHelper
{
    /**
     * @var AttributeGroupRepository
     */
    protected $attributeGroupRepository;


    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->attributeGroupRepository = new AttributeGroupRepository();
        $this->attributeRepository = new AttributeRepository();
    }

    /**
     * @return bool
     */
    public function isInsuranceAllowedInProductPage()
    {
        return (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, false)
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_ALLOW_INSURANCE, false)
            && (bool) (int) SettingsHelper::get(ConstantsHelper::ALMA_ACTIVATE_INSURANCE, false);
    }


    /**
     * @param int $languageId
     * @return \ProductCore
     * @throws InsuranceNotFoundException
     */
    public function getDefaultInsuranceProduct($languageId)
    {
        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $languageId
        );

        if(!$insuranceProductId) {
            // @todo la recréer ? envoyer un message
            throw new InsuranceNotFoundException();
        }

        /**
         * @var \ProductCore $defaultInsuranceProduct
         */
        return new \ProductCore((int)$insuranceProductId);
    }

    /**
     * @param int $languageId
     * @return string
     * @throws InsuranceNotFoundException
     */
    public function getInsuranceAttributeGroupId($languageId) {
        $attributeGroupId = $this->attributeGroupRepository->getAttributeIdByName(
            ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME,
            $languageId
        );

        if(!$attributeGroupId) {
            // @todo la recréer ? envoyer un message
            throw new InsuranceNotFoundException();
        }

        return $attributeGroupId;
    }

    /**
     * @param string $insuranceName
     * @param int $attributeGroupId
     * @param int $languageId
     * @return int
     */
    public function createOrGetInsuranceAttributeId($insuranceName, $attributeGroupId, $languageId)
    {
        $insuranceAttributeId = $this->attributeRepository->getAttributeIdByNameAndGroup(
            $insuranceName,
            $attributeGroupId,
            $languageId
        );

        if(!$insuranceAttributeId) {
            /**
             * @var \AttributeCore $testNewAttribute
             */
            $insuranceAttribute = new \AttributeCore();

            // @todo fix error statuc
            $insuranceAttribute->name = \PrestaShop\PrestaShop\Adapter\Import\ImportDataFormatter::createMultiLangField($insuranceName);
            $insuranceAttribute->id_attribute_group = $attributeGroupId;
            $insuranceAttribute->add();

            $insuranceAttributeId = $insuranceAttribute->id;
        }

        return $insuranceAttributeId;
    }

    /**
     * @param \ProductCore $defaultInsuranceProduct
     * @param string $insuranceName
     * @param float $insurancePrice
     * @param int $insuranceAttributeId
     * @return \CombinationCore
     */
    public function createOrGetInsuranceCombination($defaultInsuranceProduct, $insuranceName, $insurancePrice, $insuranceAttributeId)
    {
        // Check if the combination already exists
        /**
         * @var \CombinationCore $combination
         */
        $idProductAttribute =   \CombinationCore::getIdByReference($defaultInsuranceProduct->id, $insuranceName);

        if(! $idProductAttribute) {
            $idProductAttribute = $defaultInsuranceProduct->addCombinationEntity(
                $insurancePrice,
                $insurancePrice,
                0,
                1,
                0,
                1,
                0, // @todo fix
                $insuranceName,
                0,
                '',
                0
            );
        }

        $combination = new \CombinationCore((int)$idProductAttribute);
        $combination->setAttributes(array($insuranceAttributeId));

        return $combination;
    }
}
