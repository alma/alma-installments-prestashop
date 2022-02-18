<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\OrderDataTrait;
use Alma\PrestaShop\Utils\Settings;
use Order;
use PrestaShopDatabaseException;
use PrestaShopException;

final class StateHookController extends AdminHookController
{
    use OrderDataTrait;

    /**
     * @param $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function run($params)
    {
        if (!Settings::isRefundEnabledByState()) {
            return;
        }

        $order = new Order($params['id_order']);
        $newStatus = $params['newOrderStatus'];
        if ($order->module !== 'alma' || !$order_payment = $this->getOrderPaymentOrFail($order)) {
            return;
        }

        $id_payment = $order_payment->transaction_id;
        if ($newStatus->id == Settings::getRefundState()) {
            $alma = ClientHelper::defaultInstance();
            if (!$alma) {
                return;
            }

            try {
                $alma->payments->refund($id_payment, true);
            } catch (RequestError $e) {
                $msg = "[Alma] ERROR when creating refund for Order {$order->id}: {$e->getMessage()}";
                Logger::instance()->error($msg);

                return;
            }
        }
    }
}
