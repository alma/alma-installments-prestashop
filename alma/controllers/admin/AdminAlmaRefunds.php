<?php

/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

use Alma\PrestaShop\API\ClientHelper;

final class AdminAlmaRefundsController extends ModuleAdminController
{

    /** @var Order */
    public $order;
    public $success = null;
    public $error = null;
    /** @var array */
    protected $statuses_array = array();
    public $toolbar_title;


    public function __construct()
    {

        $this->bootstrap = true;
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->deleted = false;
        $this->explicitSelect = false;
        $this->_defaultOrderBy = 'id_order';
        $this->allow_export = false;
        $this->list_no_link  = true;
        $this->context = Context::getContext();

        parent::__construct();

        $orderID = Tools::getValue('id_order');
        if ($orderID) {
            $this->order = new Order((int)$orderID);
            if (!Validate::isLoadedObject($this->order)) {
                $this->errors[] = Tools::displayError('The order cannot be found within your database.');
                return false;
            }
        }


        $this->_select = 'a.`id_order`, a.`id_currency`, a.`reference`, a.`id_order` as refund , a.`date_add`, c.`email`, c.`firstname`, c.`lastname`, osl.`name`, IF(a.valid, 1, 0) badge_success, os.`color`,';
        $this->_use_found_rows = true;
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = a.`current_state`)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (osl.`id_order_state` = a.`current_state`)';
        $this->_where .= " AND a.`module` LIKE 'alma' ";
        $this->_where .= " AND osl.`id_lang` =  {$this->context->language->id}";
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->module->l('ID', 'AdminAlmaRefunds'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'reference' => array(
                'title' => $this->module->l('Reference', 'AdminAlmaRefunds'),
                'filter_key' => 'reference',
                'width' => 100,
            ),
            'email' => array(
                'title' => $this->module->l('Email', 'AdminAlmaRefunds'),
                'filter_key' => 'c!email',
            ),
            'firstname' => array(
                'title' => $this->module->l('First Name', 'AdminAlmaRefunds'),
                'filter_key' => 'c!firstname',
                'width' => 100,
            ),
            'lastname' => array(
                'title' => $this->module->l('Last Name', 'AdminAlmaRefunds'),
                'filter_key' => 'c!lastname',
                'width' => 100,
            )
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge($this->fields_list, array(
                'company' => array(
                    'title' => $this->l('Company'),
                    'filter_key' => 'c!company'
                ),
            ));
        }

        $this->fields_list = array_merge($this->fields_list, array(
            'total_paid_tax_incl' => array(
                'title' => $this->module->l('Amount', 'AdminAlmaRefunds'),
                'filter_key' => 'a!amount',
                'type' => 'price',
                'align' => 'text-right',
                'currency' => true,
                'callback' => 'setOrderCurrency',
                'badge_success' => true
            ),
            'name' => array(
                'title' => $this->module->l('Status', 'AdminAlmaRefunds'),
                'filter_key' => 'os!id_order_state',
                'list' => $this->statuses_array,
                'filter_type' => 'int',
                'type' => 'select',
                'color' => 'color',
            ),
            'date_add' => array(
                'title' => $this->module->l('Date', 'AdminAlmaRefunds'),
                'filter_key' => 'a!date_add',
                'type' => 'datetime',
                'align' => 'text-right',
            ),
            'refund' => array(
                'title' => $this->module->l('Alma refund', 'AdminAlmaRefunds'),
                'callback' => 'getRefund',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'orderby' => false,
                'filter' => false,
                'search' => false,

            ),
        ));

        if (!$orderID) {
            $this->context->controller->warnings[] = $this->module->l('You may process Alma refunds from this list of orders paid with our Alma module.', 'AdminAlmaRefunds');
        }
    }

    public static function setOrderCurrency($echo, $tr)
    {

        $order = new Order($tr['id_order']);

        return almaFormatPrice(almaPriceToCents($echo), (int)$order->id_currency);
    }

    public function getRefund($id_order)
    {

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            return "<a href='{$this->context->link->getAdminLink('AdminAlmaRefunds')}&id_order={$id_order}&vieworder'><span class='icon-refund'></span></a>";
        } else {
            return "<a href='{$this->context->link->getAdminLink('AdminAlmaRefunds')}&id_order={$id_order}&vieworder'><i class='icon-exchange'></i></a>";
        }
    }

    public function initToolbar()
    {

        if ($this->display == 'view') {
            $this->toolbar_title = sprintf($this->module->l('Alma refund for order reference %1$s (ID: %2$d)', 'AdminAlmaRefunds'), $this->order->reference, $this->order->id);
        }
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }


    public function renderView()
    {

        if (is_callable('Media::getMediaPath')) {
            $iconPath = Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/logos/alma_tiny.svg');
        } else {
            $iconPath = $this->module->getPathUri() . '/views/img/logos/alma_tiny.svg';
        }

        $customer = new Customer((int)$this->order->id_customer);
        $currency = new Currency($this->order->id_currency);
        $amountRefund = Tools::getValue('amount');

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $padding = "alma-pt-3";
            $formGroup = "form-group-15";
            $pullRight = "pull-right-15";
            $pullLeft = "pull-left-15";
            $panel = "panel15";
            $labelRadio = "t";
        } else {
            $padding = "alma-pt-8";
            $formGroup = "form-group";
            $pullRight = "pull-right";
            $pullLeft = "pull-left";
            $panel = "panel";
            $labelRadio = "";
        }

        $css = [
            'padding'       => $padding,
            'formGroup'     => $formGroup,
            'pullRight'     => $pullRight,
            'pullLeft'      => $pullLeft,
            'panel'         => $panel,
            'labelRadio'    => $labelRadio,
        ];

        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/refunds.tpl"
        );
        $orderData = [
            'id' => $this->order->id,
            'reference'         => $this->order->reference,
            'maxAmount'         => almaFormatPrice(almaPriceToCents($this->order->total_paid_tax_incl), (int)$this->order->id_currency),
            'amountRefund'      => $amountRefund,
            'currencySymbole'   => $currency->sign,
            'status'            => $this->statuses_array[$this->order->current_state],
        ];
        $customerData = [
            'email'     => $customer->email,
            'firstName' => $customer->firstname,
            'lastName'  => $customer->lastname,
        ];
        $tpl->assign(array(
            'iconPath'  => $iconPath,
            'moduleUrl' => $this->context->link->getAdminLink('AdminAlmaRefunds'),
            'success'   => $this->success,
            'error'     => $this->error,
            'order'     => $orderData,
            'customer'  => $customerData,
            'css'       => $css,

        ));

        return parent::renderView() . $tpl->fetch();
    }


    public function postProcess()
    {

        if (Tools::isSubmit('refundType') && Tools::isSubmit('orderID')) {
            $orderID = Tools::getValue('orderID');
            $order = new Order($orderID);
            $refundType = Tools::getValue('refundType');
            if (!$order_payment = $this->getCurrentOrderPayment($order)) {
                return;
            }
            $id_payment = $order_payment->transaction_id;
            if ($refundType == "partial") {
                $amount = Tools::getValue('amount');
                $is_total = false;
                if ($amount > $order->total_paid_tax_incl) {
                    $this->error = $this->module->l('ERROR: Amount is too high', 'AdminAlmaRefunds');

                    return;
                } elseif ($amount === $order->total_paid_tax_incl) {
                    $is_total = true;
                }
            } else {
                $amount = $order->total_paid_tax_incl;
                $is_total = true;
            }
            if ($this->runRefund($id_payment, $amount, $is_total) && $is_total === true) {
                $current_order_state = $order->getCurrentOrderState();
                if ($current_order_state->id !== 7) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->changeIdOrderState(7, (int)($order->id));
                }
            }
        }
    }

    private function getCurrentOrderPayment(Order $order)
    {

        if ('alma' != $order->module && 1 == $order->valid) {
            return false;
        }
        $order_payments = OrderPayment::getByOrderReference($order->reference);
        if ($order_payments && isset($order_payments[0])) {
            return $order_payments[0];
        }
        return false;
    }


    protected function runRefund($id_payment, $amount, $is_total)
    {
        $alma = ClientHelper::defaultInstance();
        if (!$alma) {
            return false;
        }
        try {
            $alma->payments->refund($id_payment, $is_total, almaPriceToCents($amount));
            $this->success = sprintf($this->module->l('Your refund for Order %d has been made !', 'AdminAlmaRefunds'), $this->order->id);
            return true;
        } catch (RequestError $e) {
            $msg = "[Alma] ERROR when creating refund for Order {$this->sorder->id}: {$e->getMessage()}";
            $this->error = sprintf($this->module->l('ERROR when creating refund for order %d', 'AdminAlmaRefunds'), $this->order->id);
            AlmaLogger::instance()->error($msg);

            return false;
        }
    }
}
