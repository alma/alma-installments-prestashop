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

namespace Alma\PrestaShop\Proxy;

use Alma\PrestaShop\Helpers\ToolsHelper;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleProxy
{
    /**
     * @var ToolsHelper
     */
    private $toolsHelper;

    /**
     * @param $toolsHelper
     */
    public function __construct($toolsHelper = null)
    {
        if (!$toolsHelper) {
            $toolsHelper = new ToolsHelper();
        }
        $this->toolsHelper = $toolsHelper;
    }

    /**
     * @return false|\Module
     */
    public function getModule($moduleName)
    {
        return \Module::getInstanceByName($moduleName);
    }

    /**
     * @return array
     */
    public function getModulesInstalled()
    {
        return \Module::getModulesInstalled();
    }

    /**
     * Check if module is installed.
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function isInstalled($moduleName)
    {
        if ($this->toolsHelper->psVersionCompare('1.7', '<')) {
            return $this->isInstalledBefore17($moduleName);
        }

        return $this->isInstalledAfter17($moduleName);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $moduleName
     *
     * @return bool
     */
    public function isInstalledBefore17($moduleName)
    {
        return (bool) \Module::isInstalled($moduleName);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param $moduleName
     *
     * @return bool
     */
    public function isInstalledAfter17($moduleName)
    {
        return ModuleManagerBuilder::getInstance()->build()->isInstalled($moduleName);
    }

    /**
     * @param \Module $module
     *
     * @return string
     */
    public function getModuleVersion($module)
    {
        return $module->version;
    }
}
