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

class AdminAlmaInsuranceOrdersController extends ModuleAdminController
{
    protected $actions_available = ['show'];


    /**
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->table = 'alma_insurance_product';
        $this->context = Context::getContext();
        $this->list_no_link = true;

        $this->bootstrap = true;
        parent::__construct();

        $this->meta_title = $this->module->l('Orders with insurance');

        $this->fields_list = [
            'id_order' => [
                'title' => $this->module->l('Id Order'),
                'type' => 'text',
            ],
            'reference' => [
                'title' => $this->module->l('Reference'),
                'type' => 'text',
            ],
            'status' => [
                'title' => $this->module->l('Order Status'),
                'type' => 'text',
            ],
            'customer' => [
                'title' => $this->module->l('Customer'),
                'type' => 'text',
            ],
            'nb_insurance' => [
                'title' => $this->module->l('Nb Insurances'),
                'type' => 'text',
            ],
            'date' => [
                'title' => $this->module->l('Date'),
                'type' => 'text',
            ],
            'mode' => [
                'title' => $this->module->l('Mode'),
                'type' => 'text',
            ],
        ];
    }

    /**
     * AdminController::renderList() override.
     *
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        $this->addRowAction('show');

        return parent::renderList();
    }

    /**
     * @param int $id_lang
     * @param string $orderBy
     * @param string $orderWay
     * @param int $start
     * @param int $limit
     * @param null $id_lang_shop
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @deprecated
     */
    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        $orderBy = 'date_add';
        $orderWay = 'DESC';

        $this->_group = 'GROUP BY `id_order`';
        $this->_where = ' AND `id_order` is NOT NULL ';
        $this->_select = ' count(`id_order`) as nb_insurance ';


        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

        foreach($this->_list as $key => $details)  {

            foreach($details as $value) {

                /**
                 * @var OrderCore $order
                 */
                $order = new \Order($details['id_order']);
                $this->_list[$key]['reference'] = $order->reference;

                $this->_list[$key]['status'] = $order->getCurrentStateFull($this->context->language->id)['name'];

                /**
                 * @var CustomerCore $customer
                 */
                $customer = $order->getCustomer();
                $this->_list[$key]['customer'] = sprintf("%s %s", $customer->lastname, $customer->firstname);
                $this->_list[$key]['date'] = $order->date_add;
            }
        }
    }


    /**
     * @param string $token
     * @param int $id
     * @param string $name
     * @return mixed
     */
    public function displayShowLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');

        $link = new LinkCore();

        $linkToController = $link->getAdminLink(
            'AdminAlmaInsuranceOrdersDetails',
            true,
            [],
            [
                'identifier' => $id
            ]
        );

        $tpl->assign(array(
            'href' => $linkToController,
            'action' => 'Show',
            'id' => $id
        ));

        return $tpl->fetch();
    }

    /**
     * @param int $idProduct
     * @param int $idProductAttribute
     * @return string
     */
    protected function getProductName($idProduct, $idProductAttribute = null)
    {
        /**
         * @var ProductCore $product
         */
        $product = new \Product($idProduct);
        $productName = $product->name[$this->context->language->id];

        if(null !== $idProductAttribute) {
            /*
             * @var CombinationCore $combinationProduct;
             */
            $combinationProduct = new \Combination($idProductAttribute);

            $nameDetails = $combinationProduct->getAttributesName($this->context->language->id);
            foreach ($nameDetails as $nameDetail) {
                $productName .= ' - ' . $nameDetail['name'];
            }
        }

        return $productName;
    }

    /**
     * @return void
     */
    public function initToolbar()
    {
        parent::initToolbar();

        unset( $this->toolbar_btn['new'] );
    }
}
