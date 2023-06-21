<?php
/**
 * 2018-2023 Alma SAS
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

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Utils\Logger;
use Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class HookHelper
 */
class HookHelper
{
    /**
     * @var array
     */
    protected $hooks;

    public function __construct()
    {
        $this->hooks = Hook::getHooks();
    }

    /**
     * @var array
     */
    protected static $almaHooks = [
        'header',
        'Header',
        'displayHeader',
        'displayBackOfficeHeader',
        'displayShoppingCartFooter',
        'actionOrderStatusPostUpdate',
        'displayAdminAfterHeader',
        'displayAdminOrderMain', // >= 1.7.7.0
        'displayAdminOrder',
        'paymentOptions', // >= 1.7
        'paymentReturn', // >= 1.7
        'displayPayment',
        'displayPaymentEU',
        'displayPaymentReturn',
        'displayProductPriceBlock', // >= 1.6
        'displayProductButtons',
    ];

    /**
     * Get the hooks
     *
     * @return array The completed hooks
     */
    protected function getAlmaHooks()
    {
        $almaHooks = self::$almaHooks;

        foreach ($this->hooks as $hook) {
            if (empty($hook['name'])) {
                Logger::instance()->warning(sprintf('[Alma] Empty hook name, hook :  %s', json_encode($hook)));
                continue;
            }

            $almaHooks = $this->addHooks($hook['name'], $almaHooks);
        }

        return $almaHooks;
    }

    /**
     * Generate array of hooks to inject
     *
     * @param string $hookName The hook name
     * @param array $almaHooks The alma hooks
     *
     * @return array
     */
    protected function addHooks($hookName, $almaHooks)
    {
        switch ($hookName) {
            case 'displayHeader':
                $oldHooks = ['header', 'Header'];

                break;
            case 'displayAdminOrderMain':
                $oldHooks = ['displayAdminOrder'];

                break;
            case 'displayProductPriceBlock':
                $oldHooks = ['displayProductButtons'];

                break;
            default:
                Logger::instance()->warning(sprintf('[Alma] Unknown hook name : %s', $hookName));
                return $almaHooks;
        }

        return array_diff($almaHooks, $oldHooks);
    }

    /**
     * Register the alma hooks
     *
     * @return array
     */
    public function almaRegisterHooks()
    {
        $almaHooks = $this->getAlmaHooks();
        $hooksToRegister = [];

        foreach ($this->hooks as $hook) {
            if (in_array($hook['name'], $almaHooks)) {
                $hooksToRegister[] = $hook['name'];
            }
        }

        return $hooksToRegister;
    }
}
