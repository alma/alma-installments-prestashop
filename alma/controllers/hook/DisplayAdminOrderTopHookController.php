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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Services\InsuranceService;

class DisplayAdminOrderTopHookController extends FrontendHookController
{
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var InsuranceService
     */
    protected $insuranceService;

    public function __construct($module)
    {
        parent::__construct($module);

        $this->insuranceHelper = new InsuranceHelper();
        $this->insuranceService = new InsuranceService();
    }

    public function canRun()
    {
        return parent::canRun() && $this->insuranceHelper->isInsuranceActivated();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function run($params)
    {
        /**
         * @var \OrderCore $order
         */
        $order = new \Order($params['id_order']);

        if (!$this->insuranceHelper->canRefundOrder($order)) {
            $link = $this->insuranceService->getLinkToOrderDetails($order);

            $text = sprintf(
                $this->module->l('This basket includes one or more insurance subscriptions. Make sure that the subscription(s) is canceled before proceeding with the refund. %1$sManage the cancellation directly here.%2$s', 'DisplayAdminOrderTopHookController'),
                '<a href="' . $link . '">',
                '</a>'
            );

            $this->context->smarty->assign(
                [
                    'text' => $text,
                ]
            );

            return $this->module->display($this->module->file, 'displayAdminOrderTop.tpl');
        }
    }
}
