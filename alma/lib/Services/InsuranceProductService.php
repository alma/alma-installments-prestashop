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

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

class InsuranceProductService
{

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var \ContextCore
     */
    protected $context;

    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;

    /**
     * @var AttributeGroupProductService
     */
    protected $attributeGroupProductService;

    /**
     * @var CombinationProductAttributeService
     */
    protected $combinationProductAttributeService;

    /**
     * @var AttributeProductService
     */
    protected $attributeProductService;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->context = \Context::getContext();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->attributeGroupProductService = new AttributeGroupProductService();
        $this->attributeProductService = new AttributeProductService();
        $this->combinationProductAttributeService = new CombinationProductAttributeService();

    }

    /**
     * @return \Product|\ProductCore
     */
    public function getProductInsurance()
    {
        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if (!$insuranceProductId) {
            return $this->productRepository->createInsuranceProduct();
        }

        return $this->productRepository->getProduct($insuranceProductId);
    }

    /**
     * @param int $quantity
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $idCustomization
     * @param int $idProductToAssociate
     * @param int $idProductAttributeToAssocation
     * @param float $price
     * @return void
     */
    public function addAssociations($quantity, $idProduct, $idProductAttribute, $idCustomization, $idProductToAssociate, $idProductAttributeToAssocation, $price)
    {
        for ($nbQuantity = 1; $nbQuantity <= $quantity; $nbQuantity++) {
            var_dump('add association');
            $this->almaInsuranceProductRepository->add(
                $this->context->cart->id,
                $idProduct,
                $this->context->shop->id,
                $idProductAttribute,
                $idCustomization,
                $idProductToAssociate,
                $idProductAttributeToAssocation,
                $price
            );
        }
    }


    /**
     * @param int $idProduct
     * @param \ProductCore $insuranceProduct
     * @param float $insurancePrice
     * @param string $insuranceName
     * @param int $quantity
     * @param int $idCustomization
     * @param bool $destroyPost
     * @return void
     */
    public function addInsuranceProduct($idProduct, $insuranceProduct, $insurancePrice, $insuranceName, $quantity, $idCustomization, $destroyPost = true)
    {
        $idProductAttribute = $this->attributeProductService->getIdProductAttributeFromPost($idProduct);

        $insuranceAttributeGroupId = $this->attributeGroupProductService->getIdAttributeGroupByName(
            ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME
        );

        $insuranceAttributeId = $this->attributeProductService->getAttributeId(
            $insuranceName,
            $insuranceAttributeGroupId
        );

        // Check if the combination already exists
        $idProductAttributeInsurance = $this->combinationProductAttributeService->manageCombination(
            $insuranceProduct,
            $insuranceAttributeId,
            $insuranceName,
            $insurancePrice
        );

        if($destroyPost) {
            $_POST['alma_insurance_price'] = null;
            $_POST['alma_insurance_name'] = null;
        }

        $this->context->cart->updateQty($quantity, $insuranceProduct->id, $idProductAttributeInsurance);

        $this->addAssociations(
            $quantity,
            $idProduct,
            $idProductAttribute,
            $idCustomization,
            $insuranceProduct->id,
            $insuranceAttributeId,
            $insurancePrice
        );
    }

    /**
     * @param int $idProduct
     * @param float $insurancePrice
     * @param string $insuranceName
     * @param int $quantity
     * @param int $idCustomization
     * @param bool $destroyPost
     * @return void
     */
    public function handleProductInsurance($idProduct, $insurancePrice, $insuranceName, $quantity, $idCustomization, $destroyPost = true)
    {
        // @todo Check elibilibilty
        $insuranceProduct = $this->getProductInsurance();

        if ($idProduct !== $insuranceProduct->id) {
            $this->addInsuranceProduct($idProduct, $insuranceProduct, $insurancePrice, $insuranceName, $quantity, $idCustomization, $destroyPost);
        }
    }
}
