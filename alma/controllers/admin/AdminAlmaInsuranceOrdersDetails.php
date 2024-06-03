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
if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Builders\Admin\InsuranceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Traits\AjaxTrait;

class AdminAlmaInsuranceOrdersDetailsController extends ModuleAdminController
{
    use AjaxTrait;

    /**
     * @var \Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository
     */
    protected $insuranceRepository;

    /**
     * @var ProductHelper
     */
    protected $productHelper;

    /**
     * @var Alma
     */
    public $module;
    /**
     * @var InsuranceHelper
     */
    protected $adminInsuranceHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->insuranceRepository = new AlmaInsuranceProductRepository();
        $this->productHelper = new ProductHelper();
        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->adminInsuranceHelper = $insuranceHelperBuilder->getInstance();
        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        parent::__construct();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign($this->buildData());

        $content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'alma/views/templates/admin/insurance_order_details.tpl'
        );

        $this->context->smarty->assign([
            'content' => $this->content . $content,
        ]);
    }

    /**
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function buildData()
    {
        $data = [
            'token' => \Tools::getAdminToken(ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME),
        ];

        $idLine = \Tools::getValue('identifier');

        $subscriptionEntry = $this->insuranceRepository->getById($idLine);
        $subscriptions = $this->insuranceRepository->getByOrderId($subscriptionEntry['id_order']);

        /** @var OrderCore $order */
        $order = new \Order($subscriptionEntry['id_order']);
        $data['orderId'] = $order->id;
        $data['orderReference'] = $order->reference;
        $data['mode'] = $subscriptionEntry['mode'];
        $data['orderDate'] = $order->date_add;
        $data['firstName'] = $order->getCustomer()->firstname;
        $data['lastName'] = $order->getCustomer()->lastname;
        $data['cancelUrl'] = $this->context->link->getModuleLink('alma', 'subscription');

        $data = $this->buildSubscriptions($subscriptions, $data);

        $result['dataSubscriptions'] = json_encode($data);
        $result['scriptUrl'] = $this->module->_path . 'views/js/admin/alma-insurance-subscriptions.js';
        $result['modalScriptUrl'] = $this->adminInsuranceHelper->envUrl() . ConstantsHelper::SCRIPT_MODAL_WIDGET_INSURANCE_PATH;
        $result['iframeUrl'] = $this->adminInsuranceHelper->envUrl() . ConstantsHelper::BO_IFRAME_SUBSCRIPTION_INSURANCE_PATH;

        return $result;
    }

    /**
     * @param array $subscriptions
     * @param array $data
     *
     * @return array
     */
    public function buildSubscriptions($subscriptions, $data)
    {
        foreach ($subscriptions as $subscription) {
            $dataSubscriptions = [];
            $dataSubscriptions['id'] = $subscription['id_alma_insurance_product'];

            /**
             * @var ProductCore $product
             */
            $product = new \Product($subscription['id_product']);

            $dataSubscriptions['productName'] = $this->productHelper->getProductName(
                $product,
                $this->context->language->id,
                $subscription['id_product_attribute']
            );

            /**
             * @var ProductCore $insuranceProduct
             */
            $insuranceProduct = new \Product($subscription['id_product_insurance']);

            $dataSubscriptions['insuranceName'] = $this->productHelper->getProductName(
                $insuranceProduct,
                $this->context->language->id,
                $subscription['id_product_attribute_insurance']
            );

            $dataSubscriptions['status'] = $subscription['subscription_state'];
            $dataSubscriptions['productPrice'] = $subscription['product_price'];
            $dataSubscriptions['subscriptionAmount'] = $this->priceHelper->convertPriceFromCents(
                $this->priceHelper->convertPriceToCents($subscription['price'])
            );
            $dataSubscriptions['isRefunded'] = (bool) $subscription['is_refunded'];
            $dataSubscriptions['reasonForCancelation'] = $subscription['reason_of_cancelation'];
            $dataSubscriptions['dateOfCancelation'] = $subscription['date_of_cancelation'];

            if ('0000-00-00 00:00:00' === $dataSubscriptions['dateOfCancelation']) {
                $dataSubscriptions['dateOfCancelation'] = '';
            }

            $dataSubscriptions['dateOfCancelationRequest'] = $subscription['date_of_cancelation_request'];

            if ('0000-00-00 00:00:00' === $dataSubscriptions['dateOfCancelationRequest']) {
                $dataSubscriptions['dateOfCancelationRequest'] = '';
            }

            $dataSubscriptions['subscriptionBrokerId'] = $subscription['subscription_broker_id'];
            $dataSubscriptions['subscriptionBrokerReference'] = $subscription['subscription_broker_reference'];
            $dataSubscriptions['subscriptionId'] = $subscription['subscription_id'];

            $data['cmsSubscriptions'][] = $dataSubscriptions;
        }

        return $data;
    }
}
