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

use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Traits\AjaxTrait;

class AdminAlmaInsuranceOrdersDetailsController extends ModuleAdminController
{
    use AjaxTrait;

    /**
     * @var \Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository
     */
    private $insuranceRepository;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->insuranceRepository = new \Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository();

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

        $data = $this->buildData();

        $this->context->smarty->assign($data);

        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'alma/views/templates/admin/insurance_order_details.tpl');

        $this->context->smarty->assign([
            'content' => $this->content . $content,
        ]);
    }

    public function buildData()
    {
        $data = [
            'token' => \Tools::getAdminTokenLite(ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_DETAILS_CLASSNAME),
        ];

        $idLine = \Tools::getValue('identifier');
        $order = $this->insuranceRepository->getById($idLine);
        $orders = $this->insuranceRepository->getByOrderId($order['id_order']);

        foreach($orders as $order)
        {
            $product = new \ProductCore($order['id_product']);
            $productAttribute = null;
            if(null !== $order['id_product_attribute']) {
                $productAttribute = new \CombinationCore((int)$order['id_product_attribute']);
            }

            $data['product_name'] = $product->name[$this->context->language->id];
        }

    }

}
