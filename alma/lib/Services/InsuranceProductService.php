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
use Alma\PrestaShop\Exceptions\InsuranceContractException;
use Alma\PrestaShop\Factories\CombinationFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\LinkFactory;
use Alma\PrestaShop\Factories\ProductFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\AlmaCartItemModel;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InsuranceProductService
{
    /**
     * @var \Context|null
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
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var InsuranceApiService
     */
    protected $insuranceApiService;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;
    /**
     * @var ImageHelper
     */
    protected $imageHelper;
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var CombinationFactory
     */
    protected $combinationFactory;
    /**
     * @var \Link
     */
    protected $link;
    /**
     * @var ToolsFactory
     */
    protected $toolsFactory;
    /**
     * @var mixed
     */
    protected $logger;

    /**
     * @param AlmaInsuranceProductRepository $almaInsuranceProductRepository
     * @param ContextFactory $contextFactory
     * @param LinkFactory $linkFactory
     * @param AttributeGroupProductService $attributeGroupProductService
     * @param AttributeProductService $attributeProductService
     * @param CombinationProductAttributeService $combinationProductAttributeService
     * @param InsuranceService $insuranceService
     * @param CartService $cartService
     * @param ProductRepository $productRepository
     * @param ProductHelper $productHelper
     * @param InsuranceApiService $insuranceApiService
     * @param PriceHelper $priceHelper
     * @param InsuranceHelper $insuranceHelper
     * @param ImageHelper $imageHelper
     * @param ToolsHelper $toolsHelper
     */
    public function __construct(
        $productFactory,
        $combinationFactory,
        $linkFactory,
        $almaInsuranceProductRepository,
        $contextFactory,
        $attributeGroupProductService,
        $attributeProductService,
        $combinationProductAttributeService,
        $insuranceService,
        $cartService,
        $productRepository,
        $productHelper,
        $insuranceApiService,
        $priceHelper,
        $insuranceHelper,
        $toolsFactory,
        $imageHelper,
        $toolsHelper
    ) {
        $this->productFactory = $productFactory;
        $this->combinationFactory = $combinationFactory;
        $this->link = $linkFactory->create();
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository;
        $this->context = $contextFactory->getContext();
        $this->attributeGroupProductService = $attributeGroupProductService;
        $this->attributeProductService = $attributeProductService;
        $this->combinationProductAttributeService = $combinationProductAttributeService;
        $this->insuranceService = $insuranceService;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->insuranceApiService = $insuranceApiService;
        $this->priceHelper = $priceHelper;
        $this->insuranceHelper = $insuranceHelper;
        $this->toolsFactory = $toolsFactory;
        $this->imageHelper = $imageHelper;
        $this->toolsHelper = $toolsHelper;
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
     * @param array $insuranceContractInfos
     *
     * @return void
     *
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
        $idAddressDelivery,
        $insuranceContractInfos
    ) {
        for ($nbQuantity = 1; $nbQuantity <= $quantity; ++$nbQuantity) {
            $this->almaInsuranceProductRepository->add(
                $this->context->cart->id,
                $idProduct,
                $this->context->shop->id,
                $idProductAttribute,
                $idCustomization,
                $idProductToAssociate,
                $idProductAttributeToAssocation,
                $this->priceHelper->convertPriceToCents($price),
                $idAddressDelivery,
                $insuranceContractInfos
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
     * @param array $insuranceContractInfos
     * @param \Cart|null $cart
     *
     * @return void
     *
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
        $insuranceContractInfos,
        $cart = null
    ) {
        $insuranceName = str_replace('insurance_contract_', '', $insuranceName);
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
            $insurancePrice,
            $quantity,
            $this->context->shop->id
        );

        if (null === $this->context->cart) {
            // There is a bug in some versions of Prestashop when adding a product on the cart and be sign-in and not having a default address
            // In this case in the actionCartSaveHook, the cart in the context is null
            $this->context->cart = $cart;
            $this->cartService->updateQty($quantity, $insuranceProduct->id, $idProductAttributeInsurance, $cart, false, 'up', 0, null, true, true);
        } else {
            $this->context->cart->updateQty($quantity, $insuranceProduct->id, $idProductAttributeInsurance, false, 'up', 0, null, true, true);
        }

        $this->addAssociations(
            $quantity,
            $idProduct,
            $idProductAttribute,
            $idCustomization,
            $insuranceProduct->id,
            $idProductAttributeInsurance,
            $insurancePrice,
            $this->context->cart->id_address_delivery,
            $insuranceContractInfos
        );
    }

    /**
     * @param int $idProduct
     * @param int $insuranceContractId
     * @param int $quantity
     * @param int $idCustomization
     * @param \Cart|null $cart
     *
     * @return void
     */
    public function addInsuranceProductInPsCart($idProduct, $insuranceContractId, $quantity, $idCustomization, $cart = null)
    {
        try {
            $idProductAttribute = $this->attributeProductService->getIdProductAttributeFromPost($idProduct);

            $cmsReference = $this->insuranceHelper->createCmsReference($idProduct, $idProductAttribute);
            $staticPrice = $this->productHelper->getPriceStatic($idProduct, $idProductAttribute);
            $staticPriceInCents = $this->priceHelper->convertPriceToCents($staticPrice);

            $insuranceContract = $this->insuranceApiService->getInsuranceContract($insuranceContractId, $cmsReference, $staticPriceInCents);

            if (null === $insuranceContract) {
                throw new InsuranceContractException(sprintf('[Alma] Insurance contract not found with these insuranceContractId: %s, cmsReference: %s, staticPriceInCents: %s', $insuranceContractId, $cmsReference, $staticPriceInCents));
            }

            $insuranceProduct = $this->insuranceService->createProductIfNotExists();

            if ($idProduct !== $insuranceProduct->id) {
                $this->addInsuranceProduct(
                    $idProduct,
                    $insuranceProduct,
                    $this->priceHelper->convertPriceFromCents($insuranceContract->getPrice()),
                    $insuranceContract->getId(),
                    $quantity,
                    $idCustomization,
                    [
                        'insurance_contract_id' => $insuranceContractId,
                        'cms_reference' => $cmsReference,
                        'product_price' => $staticPriceInCents,
                    ],
                    $cart
                );
            }
        } catch (\Exception $e) {
            Logger::instance()->error(
                sprintf(
                    '[Alma] An error occured when adding an insurance, InsuranceContratId : "%s", IdProduct : "%s", message "%s", trace "%s"',
                    $insuranceContractId,
                    $idProduct,
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }
    }

    /**
     * @return bool
     */
    public function canHandleAddingProductInsuranceOnce()
    {
        if (
            $this->toolsFactory->getIsset('alma_id_insurance_contract')
            && 1 == $this->toolsFactory->getValue('add')
            && 'update' == $this->toolsFactory->getValue('action')
        ) {
            // To reset the execution context of PHP
            $_POST['alma_id_insurance_contract'] = null;

            return true;
        }

        return false;
    }

    /**
     * @param AlmaCartItemModel $product
     * @param int $cartId
     * @param string $insuranceProductId
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getItemsCartInsuranceProductAttributes($product, $cartId, $insuranceProductId)
    {
        $resultInsurance = [];

        $almaInsurancesByAttribute = $this->almaInsuranceProductRepository->getCountInsuranceProductAttributeByProductAndCartIdAndShopId(
            $product,
            $cartId,
            $this->context->shop->id
        );

        $almaInsuranceProduct = $this->productFactory->create((int) $insuranceProductId);
        $idImage = $almaInsuranceProduct->getImages($this->context->language->id)[0]['id_image'];
        $linkRewrite = $almaInsuranceProduct->link_rewrite[$this->context->language->id];

        foreach ($almaInsurancesByAttribute as $almaInsurance) {
            $almaProductAttribute = $this->combinationFactory->create((int) $almaInsurance['id_product_attribute_insurance']);
            $contractAlmaInsuranceProduct = $this->almaInsuranceProductRepository->getContractByProductAndCartIdAndShopAndInsuranceProductAttribute(
                $product,
                $cartId,
                $this->context->shop->id,
                $almaInsurance['id_product_attribute_insurance']
            );

            $resultInsurance[] = [
                'idInsuranceProduct' => $almaInsuranceProduct->id,
                'nameInsuranceProduct' => $almaInsuranceProduct->name[$this->context->language->id],
                'urlImageInsuranceProduct' => '//' . $this->link->getImageLink(
                    $linkRewrite,
                    $idImage,
                    $this->imageHelper->getFormattedImageTypeName('cart')
                ),
                'reference' => $almaProductAttribute->reference,
                'unitPrice' => $this->priceHelper->convertPriceFromCents($almaInsurance['price']),
                // TODO : Create a function to handle displayPrice for all versions of Prestashop
                // TODO : Handle the currency symbol and quantity {Context::getContext()->currentLocale->formatPrice($associatedInsurance.price * $associatedInsurance.quantity, $currency.iso_code)}
                //'price' => $this->context->currentLocale->formatPrice($this->priceHelper->convertPriceFromCents($almaInsurance['price']), $this->context->currency->iso_code),
                'price' => \Tools::displayPrice($this->priceHelper->convertPriceFromCents($almaInsurance['price'])),
                'quantity' => $almaInsurance['nbInsurance'],
                'insuranceContractId' => $contractAlmaInsuranceProduct[0]['insurance_contract_id'],
                'idsAlmaInsuranceProduct' => $this->toolsHelper->getJsonValues($contractAlmaInsuranceProduct, 'id_alma_insurance_product'),
            ];
        }

        return $resultInsurance;
    }
}
