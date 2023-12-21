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

use Alma\API\Client;
use Alma\API\Exceptions\ParamsException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Exceptions\InsuranceInstallException;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

class InsuranceProductService
{
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

    /**
     * @var InsuranceService
     */
    protected $insuranceService;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var Client|mixed|null
     */
    protected $alma;
    /**
     * @var ProductHelper
     */
    protected $productHelper;

    public function __construct()
    {
        $this->context = \Context::getContext();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->attributeGroupProductService = new AttributeGroupProductService();
        $this->attributeProductService = new AttributeProductService();
        $this->combinationProductAttributeService = new CombinationProductAttributeService();
        $this->insuranceService = new InsuranceService();
        $this->cartService = new CartService();
        $this->productRepository = new ProductRepository();
        $this->alma = ClientHelper::defaultInstance();
        $this->productHelper = new ProductHelper();
    }

    /**
     * @param int $quantity
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $idCustomization
     * @param int $idProductToAssociate
     * @param int $idProductAttributeToAssocation
     * @param float $price
     * @param int $idAddressDelivery
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function addAssociations(
        $quantity,
        $idProduct,
        $idProductAttribute,
        $idCustomization,
        $idProductToAssociate,
        $idProductAttributeToAssocation,
        $price,
        $idAddressDelivery
    )
    {
        for ($nbQuantity = 1; $nbQuantity <= $quantity; $nbQuantity++) {
            $this->almaInsuranceProductRepository->add(
                $this->context->cart->id,
                $idProduct,
                $this->context->shop->id,
                $idProductAttribute,
                $idCustomization,
                $idProductToAssociate,
                $idProductAttributeToAssocation,
                $price,
                $idAddressDelivery
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
     * @param int $idProductAttibutePS16
     * @param bool $destroyPost
     * @return void
     * @throws AlmaException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function addInsuranceProduct(
        $idProduct,
        $insuranceProduct,
        $insurancePrice,
        $insuranceName,
        $quantity,
        $idCustomization,
        $idProductAttibutePS16 = 0,
        $destroyPost = true
    )
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $idProductAttribute = $this->attributeProductService->getIdProductAttributeFromPost($idProduct);
        } else {
            $idProductAttribute = $idProductAttibutePS16;
        }

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
            $insurancePrice,
            $this->context->shop->id
        );

        if ($destroyPost) {
            $_POST['alma_id_insurance_contract'] = null;
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->cart->updateQty($quantity, $insuranceProduct->id, $idProductAttributeInsurance, false, 'up', 0, null, true, true);
        } else {
            $this->cartService->updateQty($quantity, $insuranceProduct->id, $idProductAttributeInsurance, false, 'up', 0, null, true, true);
        }

        $this->addAssociations(
            $quantity,
            $idProduct,
            $idProductAttribute,
            $idCustomization,
            $insuranceProduct->id,
            $idProductAttributeInsurance,
            $insurancePrice,
            $this->context->cart->id_address_delivery
        );
    }

    /**
     * @param $idProduct
     * @param $insuranceContractId
     * @param $quantity
     * @param $idCustomization
     * @param $idProductAttributePS16
     * @param $destroyPost
     * @return void|null
     * @throws AlmaException
     * @throws InsuranceInstallException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function handleAddingProductInsurance($idProduct, $insuranceContractId, $quantity, $idCustomization, $idProductAttributePS16 = 0, $destroyPost = true)
    {
        if (!$this->alma) {
            return null;
        }

        $idProductAttribute = $this->attributeProductService->getIdProductAttributeFromPost($idProduct);
        $cmsReference = $idProduct . '-' . $idProductAttribute;
        $regularPrice = $this->productHelper->getRegularPrice($idProduct, $idProductAttribute);
        $regularPriceInCents = PriceHelper::convertPriceToCents($regularPrice);

        try {
            $insuranceContract = $this->alma->insurance->getInsuranceContract($insuranceContractId, $cmsReference, $regularPriceInCents);
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when get insurance contract with id ={$insuranceContractId}, cms reference = {$cmsReference}, product price = {$regularPriceInCents}: {$e->getMessage()}";
            Logger::instance()->error($msg);
        } catch (ParamsException $e) {
            Logger::instance()->error($e->getMessage());
        }

        $insuranceProduct = $this->insuranceService->createProductIfNotExists();
        $insurancePriceToFloat = PriceHelper::convertPriceFromCents($insuranceContract->getPrice());

        if ($idProduct !== $insuranceProduct->id) {
            $this->addInsuranceProduct($idProduct, $insuranceProduct, $insurancePriceToFloat, $insuranceContract->getName(), $quantity, $idCustomization, $idProductAttributePS16, $destroyPost);
        }
    }

    /**
     * @param int $idProduct
     * @param int $idProductAttribute
     * @return void
     */
    public function handleRemovingProductInsurance($idProduct, $idProductAttribute)
    {
        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if($idProduct === $insuranceProductId) {
            return;
        }

        $this->insuranceService->deleteAllLinkedInsuranceProducts([
            'id_cart' => $this->context->cart->id,
            'id_product' => $idProduct,
            'id_product_attribute' => $idProductAttribute,
            'customization_id' => 0
        ]);
    }
}
