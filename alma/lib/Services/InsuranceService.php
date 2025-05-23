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

use Alma\API\Entities\Insurance\Subscription;
use Alma\API\Exceptions\MissingKeyException;
use Alma\API\Lib\ArrayUtils;
use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\ApiHelperBuilder;
use Alma\PrestaShop\Builders\Services\CartServiceBuilder;
use Alma\PrestaShop\Exceptions\InsuranceInstallException;
use Alma\PrestaShop\Exceptions\TermsAndConditionsException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @deprecated We will remove insurance
 */
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

    /**
     * @var ArrayUtils
     */
    protected $arrayUtils;
    /**
     * @var \Alma\PrestaShop\Helpers\ToolsHelper
     */
    protected $toolsHelper;
    /**
     * @var \Alma\PrestaShop\Helpers\ApiHelper
     */
    protected $apiHelper;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    protected $clientModel;
    /**
     * @var AdminInsuranceHelper
     */
    protected $insuranceHelper;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName(ConstantsHelper::ALMA_MODULE_NAME);
        $this->productRepository = new ProductRepository();
        $this->imageService = new ImageService();
        $cartServiceBuilder = new CartServiceBuilder();
        $this->cartService = $cartServiceBuilder->getInstance();
        $this->context = \Context::getContext();
        $this->attributeGroupRepository = new AttributeGroupRepository();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->insuranceApiService = new InsuranceApiService();
        $this->arrayUtils = new ArrayUtils();
        $this->toolsHelper = new ToolsHelper();
        $this->apiHelper = (new ApiHelperBuilder())->getInstance();
        $this->clientModel = new ClientModel();
        $this->insuranceHelper = (new InsuranceHelperBuilder())->getInstance();
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
                'subscription_id' => '<subscription_id>',
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
     * @throws MissingKeyException
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

        $this->arrayUtils->checkMandatoryKeys(['notice-document', 'ipid-document', 'fic-document'], $files);

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

    /**
     * @return void
     */
    public function installIfCompatible()
    {
        if ($this->toolsHelper->psVersionCompare('1.7', '>=')) {
            try {
                $this->handleInsuranceFlag();
            } catch (\PrestaShopException $e) {
                LoggerFactory::instance()->error(
                    sprintf(
                        '[Alma] Error handling insurance flag: %s',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function handleInsuranceFlag()
    {
        try {
            $isAllowInsurance = $this->apiHelper->saveFeatureFlag(
                $this->clientModel->getMerchantMe(),
                'cms_insurance',
                ConstantsHelper::ALMA_ALLOW_INSURANCE,
                ConstantsHelper::ALMA_ACTIVATE_INSURANCE
            );

            $this->insuranceHelper->handleBOMenu($isAllowInsurance);
            $this->insuranceHelper->handleDefaultInsuranceFieldValues($isAllowInsurance);
        } catch (InsuranceInstallException $e) {
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Installation of exception has failed, message "%s", trace "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }
    }
}
