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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Factories\ContextFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ThemeHelper.
 */
class ThemeHelper
{
    const CONFIG_THEME_FILE = _PS_THEME_DIR_ . 'config/theme.yml';
    /**
     * @var ContextFactory
     */
    protected $contextFactory;
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    public function __construct()
    {
        $this->contextFactory = new ContextFactory();
        $this->toolsHelper = new ToolsHelper();
    }

    /**
     * @return string
     */
    public function getThemeNameWithVersion()
    {
        $themeName = $this->contextFactory->getContext()->shop->theme_name;
        $themeConfigPath = self::CONFIG_THEME_FILE;

        if ($this->toolsHelper->psVersionCompare('1.7', '>=')) {
            if (file_exists($themeConfigPath)) {
                $themeConfig = \Symfony\Component\Yaml\Yaml::parseFile($themeConfigPath);
                $themeVersion = $themeConfig['version'] ?: 'undefined';
                $themeName = $themeName . ' ' . $themeVersion;
            }
        }

        return $themeName;
    }
}
