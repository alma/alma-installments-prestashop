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

namespace Alma\PrestaShop\Factories;

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ModuleFactory.
 */
class ModuleFactory
{
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    public function __construct($toolsHelper)
    {
        $this->toolsHelper = $toolsHelper;
    }

    /**
     * @return false|\Module
     */
    public function getModule()
    {
        return \Module::getInstanceByName(ConstantsHelper::ALMA_MODULE_NAME);
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        $module = $this->getModule();

        if ($module) {
            return $module->name;
        }

        return '';
    }

    /**
     * @return string|null
     */
    public function getPathUri()
    {
        $module = $this->getModule();

        if ($module) {
            return $module->getPathUri();
        }

        return '';
    }

    /**
     * Get translation for a given module text.
     *
     * Note: $specific parameter is mandatory for library files.
     * Otherwise, translation key will not match for Module library
     * when module is loaded with eval() Module::getModulesOnDisk()
     *
     * @param string $string String to translate
     * @param bool|string $specific filename to use in translation key
     * @param string|null $locale Locale to translate to
     *
     * @return string Translation
     */
    public function l($string, $specific = false, $locale = null)
    {
        $module = $this->getModule();

        if ($module) {
            return $module->l($string, $specific, $locale);
        }

        return $string;
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
}
