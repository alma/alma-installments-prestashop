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

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Proxy\ModuleProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ModuleFactory.
 */
class AlmaModuleModel
{
    const HOOK_NAME_PAYMENT_CHECKOUT_PS17 = 'paymentOptions';
    const HOOK_NAME_PAYMENT_CHECKOUT_PS16 = 'displayPayment';
    /**
     * @var ModuleProxy
     */
    private $moduleProxy;
    private $moduleName = ConstantsHelper::ALMA_MODULE_NAME;
    /**
     * @var \Db
     */
    private $db;
    /**
     * @var \DbQuery|mixed|null
     */
    private $dbQuery;

    public function __construct($moduleProxy = null, $dbQuery = null, $db = null)
    {
        if (!$moduleProxy) {
            $moduleProxy = new ModuleProxy();
        }
        $this->moduleProxy = $moduleProxy;
        if (!$dbQuery) {
            $dbQuery = new \DbQuery();
        }
        $this->dbQuery = $dbQuery;
        if (!$db) {
            $db = \Db::getInstance();
        }
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $module = $this->moduleProxy->getModule($this->moduleName);

        if ($module) {
            return $this->moduleProxy->getModuleVersion($module);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        $hookName = self::HOOK_NAME_PAYMENT_CHECKOUT_PS17;
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $hookName = self::HOOK_NAME_PAYMENT_CHECKOUT_PS16;
        }

        $query = $this->dbQuery;
        $query->select('h.id_hook, m.name, hm.position')
            ->from('hook', 'h')
            ->join('JOIN ' . _DB_PREFIX_ . 'hook_module hm ON hm.id_hook = h.id_hook')
            ->join('JOIN ' . _DB_PREFIX_ . 'module m ON m.id_module = hm.id_module')
            ->where('h.name = "' . pSQL($hookName) . '"')
            ->where('m.name = "' . pSQL($this->moduleName) . '"')
            ->orderBy('hm.position ASC');

        $almaPosition = $this->db->getRow($query);

        if (!$almaPosition) {
            return '';
        }

        return $almaPosition['position'];
    }

    /**
     * @return false|\Module
     */
    public function getModule()
    {
        return $this->moduleProxy->getModule($this->moduleName);
    }
}
