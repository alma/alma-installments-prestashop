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

use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\AdminHookController;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class ActionOrderStatusBeforeUpdateHookController extends AdminHookController
{
    public function __construct($module)
    {
        parent::__construct($module);
    }

    /**
     * Checks if user is logged in as Employee or is an API Webservice call
     *
     * When we check if is an API user, we assume that the API user has already
     * the good rights because when canRun is called, actions linked to the hook
     * were already well authenticated by PrestaShop.
     *
     * @return bool
     */
    public function canRun()
    {
        return parent::canRun() || $this->isKnownApiUser();
    }

    /**
     * Execute some trigger before on change state (refund)
     *
     * @param array $params
     *
     * @return void
     */
    public function run($params)
    {
        if (!$this->alma) {
            return;
        }

        $order = new \Order($params['id_order']);
        $newStatus = $params['newOrderStatus'];

        switch ($newStatus->id) {
            case SettingsHelper::getRefundState():
                $this->refund($order);
                break;
            default:
                break;
        }
    }
}
