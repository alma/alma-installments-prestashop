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

    public function __construct($module)
    {
        parent::__construct($module);

        $this->productRepository = new ProductRepository();
        $this->attributeGroupRepository = new AttributeGroupRepository();
        $this->attributeRepository = new AttributeRepository();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
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
            isset($_POST['alma_insurance_price'])
            && $_POST['alma_insurance_price'] != 'none'
            && isset($_POST['alma_insurance_name'])
            && $_POST['alma_insurance_name'] != 'none'
        ) {
            // @todo Check elibilibilty

            $insurancePrice = $_POST['alma_insurance_price'];
            $insuranceName = $_POST['alma_insurance_name'];

            $insuranceProductId = $this->productRepository->getProductIdByReference(
                ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
                $this->context->language->id
            );

            if (!$insuranceProductId) {
                // @todo la recréer ? envoyer un message
                throw new InsuranceNotFoundException();
            }

            /**
             * @var \ProductCore $defaultInsuranceProduct
             */
            $defaultInsuranceProduct = new \Product((int) $insuranceProductId);

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

                $insuranceAttribute->name = \PrestaShop\PrestaShop\Adapter\Import\ImportDataFormatter::createMultiLangField($insuranceName);
                $insuranceAttribute->id_attribute_group = $attributeGroupId;
                $insuranceAttribute->add();

                $insuranceAttributeId = $insuranceAttribute->id;
            }

            // Check if the combination already exists

            /**
             * @var \CombinationCore $combinaison
             */
            $idProductAttribute = \CombinationCore::getIdByReference($insuranceProductId, $insuranceName);

            if (!$idProductAttribute) {
                $idProductAttribute = $defaultInsuranceProduct->addCombinationEntity(
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

                $combinaison = new \CombinationCore((int) $idProductAttribute);
                $combinaison->setAttributes([$insuranceAttributeId]);
            }

            \StockAvailable::setQuantity($defaultInsuranceProduct->id, $idProductAttribute, 1, $this->context->shop->id);

            $_POST['alma_insurance_price'] = 'none';

            $this->context->cart->updateQty(1, $defaultInsuranceProduct->id, $idProductAttribute);

            $product = $this->context->cart->getLastProduct();

            $this->almaInsuranceProductRepository->add(
                $this->context->cart->id,
                $product['id_product'],
                $this->context->shop->id,
                $product['id_product_attribute'],
                $_POST['id_customization'],
                $insuranceProductId,
                $insuranceAttributeId,
                $insurancePrice
            );
        }

        $_POST['alma_insurance_price'] = 'none';

        // @todo suppression
    }
}
