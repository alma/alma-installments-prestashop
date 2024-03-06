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

use Alma\API\Entities\Insurance\Subscription;
use Alma\PrestaShop\Exceptions\InsuranceInstallException;
use Alma\PrestaShop\Exceptions\TermsAndConditionsException;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InsuranceService
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var ImageService
     */
    protected $imageService;
    /**
     * @var \ContextCore
     */
    protected $context;
    /**
     * @var AttributeGroupRepository
     */
    protected $attributeGroupRepository;
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var CartService
     */
    protected $cartService;
    /**
     * @var InsuranceApiService
     */
    protected $insuranceApiService;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName(ConstantsHelper::ALMA_MODULE_NAME);
        $this->productRepository = new ProductRepository();
        $this->imageService = new ImageService();
        $this->cartService = new CartService();
        $this->context = \Context::getContext();
        $this->attributeGroupRepository = new AttributeGroupRepository();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->insuranceApiService = new InsuranceApiService();
    }

    /**
     * Create the default Insurance product
     *
     * @return \ProductCore
     *
     * @throws InsuranceInstallException
     */
    public function createProductIfNotExists()
    {
        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if (!$insuranceProductId) {
            try {
                $insuranceProduct = $this->productRepository->createInsuranceProduct();
                $shops = \Shop::getShops(true, null, true);

                $this->imageService->associateImageToProduct(
                    $insuranceProduct->id,
                    $shops,
                    ConstantsHelper::ALMA_INSURANCE_PRODUCT_IMAGE_URL
                );

                return $insuranceProduct;
            } catch (\Exception $e) {
                Logger::instance()->error(
                    sprintf(
                        '[Alma] The insurance product has not been created, message "%s", trace "%s"',
                        $e->getMessage(),
                        $e->getTraceAsString()
                    )
                );

                throw new InsuranceInstallException();
            }
        }

        return $this->productRepository->getProduct($insuranceProductId);
    }

    /**
     * Create the default Insurance attribute group
     *
     * @return void
     *
     * @throws InsuranceInstallException
     */
    public function createAttributeGroupIfNotExists()
    {
        $insuranceAttributeGroup = $this->attributeGroupRepository->getAttributeIdByName(
            ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME,
            $this->context->language->id
        );

        if (!$insuranceAttributeGroup) {
            try {
                $this->attributeGroupRepository->createInsuranceAttributeGroup();
            } catch (\Exception $e) {
                Logger::instance()->error(
                    sprintf(
                        '[Alma] The insurance attribute group has not been created, message "%s", trace "%s"',
                        $e->getMessage(),
                        $e->getTraceAsString()
                    )
                );

                throw new InsuranceInstallException();
            }
        }
    }

    /**
     * @return void
     *
     * @throws InsuranceInstallException
     */
    public function installDefaultData()
    {
        if (!$this->almaInsuranceProductRepository->createTable()) {
            throw new InsuranceInstallException('The creation of table "alma_insurance_product" has failed');
        }

        $this->createProductIfNotExists();
        $this->createAttributeGroupIfNotExists();
    }

    /**
     * @param array $params
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     */
    public function deleteAllLinkedInsuranceProducts($params)
    {
        /**
         * @var \ContextCore $context
         */
        $context = \Context::getContext();

        $allInsurancesLinked = $this->almaInsuranceProductRepository->getAllByProduct(
            $params['id_cart'],
            $params['id_product'],
            $params['id_product_attribute'],
            $params['customization_id'],
            $context->shop->id
        );

        foreach ($allInsurancesLinked as $insuranceLinked) {
            // Delete insurance in cart
            $context->cart->updateQty(
                1,
                $insuranceLinked['id_product_insurance'],
                $insuranceLinked['id_product_attribute_insurance'],
                0,
                'down'
            );

            // Delete association
            $this->almaInsuranceProductRepository->deleteById($insuranceLinked['id_alma_insurance_product']);
        }
    }

    /**
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function hasInsuranceInCart()
    {
        $idsInsurances = $this->almaInsuranceProductRepository->getIdsByCartIdAndShop(
            $this->context->cart->id,
            $this->context->shop->id
        );

        if (count($idsInsurances) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param array $insuranceContracts
     * @param \Cart $cart
     *
     * @return array
     */
    public function createSubscriptionData($insuranceContracts, $cart)
    {
        $subscriptionData = [];
        $customerService = new CustomerService($cart->id_customer, $cart->id_address_invoice, $cart->id_address_delivery);

        $callbackUrl = urldecode($this->context->link->getModuleLink(
            $this->module->name,
            'subscription',
            [
                'action' => 'update',
                'sid' => '<subscription_external_id>',
                'trace' => '<trace>',
            ]
        ));

        foreach ($insuranceContracts as $insuranceContract) {
            $subscriptionData[] = new Subscription(
                $insuranceContract['insurance_contract_id'],
                $insuranceContract['price'],
                $insuranceContract['cms_reference'],
                $insuranceContract['product_price'],
                $customerService->getSubscriber(),
                $callbackUrl
            );
        }

        return $subscriptionData;
    }

    /**
     * @param array $insuranceContracts
     *
     * @return array
     *
     * @throws TermsAndConditionsException
     */
    public function createTextTermsAndConditions($insuranceContracts)
    {
        $files = [];

        foreach ($insuranceContracts as $insuranceContract) {
            $files = $this->insuranceApiService->getInsuranceContractFiles(
                $insuranceContract['insurance_contract_id'],
                $insuranceContract['cms_reference'],
                $insuranceContract['product_price']
            );

            break;
        }

        if (!empty($files)) {
            return [
                'text' => $this->getTextTermsAndConditions(),
                'link-notice' => $files['notice-document'],
                'link-ipid' => $files['ipid-document'],
                'link-fic' => $files['fic-document'],
            ];
        }

        throw new TermsAndConditionsException('An error occurred when retrieving the files');
    }

    /**
     * @return string
     */
    public function getTextTermsAndConditions()
    {
        return $this->module->l('I hereby acknowledge my acceptance to subscribe to the insurance offered by Alma. In doing so, I confirm that I have previously reviewed the [information notice, which constitutes the general conditions], the [insurance product information document], and the [pre-contractual information and advice sheet]. I ahead to it without reservation and agree to electronically sign the various documents forming my contract, if applicable. I expressly consent to the collection and use of my personal data for the purpose of subscribing to and managing my insurance contract(s).', 'InsuranceService');
    }

    /**
     * @param \OrderCore $order
     *
     * @return string
     */
    public function getLinkToOrderDetails($order)
    {
        $almaInsuranceId = $this->almaInsuranceProductRepository->getIdByOrderId($order->id, $order->id_shop);

        $link = new \LinkCore();

        $linkToController = $link->getAdminLink(
            'AdminAlmaInsuranceOrdersDetails',
            true,
            [],
            [
                'identifier' => $almaInsuranceId['id'],
            ]
        );

        return $linkToController;
    }
}
