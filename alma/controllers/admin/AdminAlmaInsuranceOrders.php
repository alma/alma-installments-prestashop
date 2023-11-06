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
    /**
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->table = 'alma_insurance_product';
        $this->context = Context::getContext();

        $this->bootstrap = true;
        parent::__construct();

        $this->fields_list = [
            'id_order' => [
                'title' => $this->module->l('Id Order'),
                'type' => 'text',
            ],
            'id_product' => [
                'title' => $this->module->l('Id Product'),
                'type' => 'text',
            ],
            'id_shop' => [
                'title' => $this->module->l('Id Shop'),
                'type' => 'text',
            ],
            'id_product_attribute' => [
                'title' => $this->module->l('Id Product Attribute'),
                'type' => 'text',
            ],
            'id_customization' => [
                'title' => $this->module->l('Id Customization'),
                'type' => 'text',
            ],
            'id_product_insurance' => [
                'title' => $this->module->l('Id Product Insurance'),
                'type' => 'text',
            ],
            'id_product_attribute_insurance' => [
                'title' => $this->module->l('Id Product Attribute Insurance'),
                'type' => 'text',
            ],
            'price' => [
                'title' => $this->module->l('Price'),
                'type' => 'text',
            ],
            'date_add' => [
                'title' => $this->module->l('Date'),
                'type' => 'text',
            ],
        ];
    }
}
