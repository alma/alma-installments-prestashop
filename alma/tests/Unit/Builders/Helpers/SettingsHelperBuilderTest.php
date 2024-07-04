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

namespace Alma\PrestaShop\Tests\Unit\Builders\Helpers;

use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ShopHelper;
use PHPUnit\Framework\TestCase;

class SettingsHelperBuilderTest extends TestCase
{
    /**
     * @var SettingsHelperBuilder
     */
    protected $settingsHelperBuilder;

    public function setUp()
    {
        $this->settingsHelperBuilder = new SettingsHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(SettingsHelper::class, $this->settingsHelperBuilder->getInstance());
    }

    public function testGetShopHelper()
    {
        $this->assertInstanceOf(ShopHelper::class, $this->settingsHelperBuilder->getShopHelper());
        $this->assertInstanceOf(ShopHelper::class, $this->settingsHelperBuilder->getShopHelper(
            new ShopHelper()
        ));
    }

    public function testGetConfigurationHelper()
    {
        $this->assertInstanceOf(ConfigurationHelper::class, $this->settingsHelperBuilder->getConfigurationHelper());
        $this->assertInstanceOf(ConfigurationHelper::class, $this->settingsHelperBuilder->getConfigurationHelper(
            new ConfigurationHelper()
        ));
    }
}
