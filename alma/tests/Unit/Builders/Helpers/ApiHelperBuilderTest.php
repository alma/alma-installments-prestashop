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

use Alma\PrestaShop\Builders\Helpers\ApiHelperBuilder;
use Alma\PrestaShop\Helpers\Admin\AdminInsuranceHelper;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class ApiHelperBuilderTest extends TestCase
{
    /**
     * @var ApiHelperBuilder
     */
    protected $apiHelperBuilder;

    public function setUp()
    {
        $this->apiHelperBuilder = new ApiHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(ApiHelper::class, $this->apiHelperBuilder->getInstance());
    }

    public function testGetClientHelper()
    {
        $this->assertInstanceOf(ClientHelper::class, $this->apiHelperBuilder->getClientHelper());
        $this->assertInstanceOf(ClientHelper::class, $this->apiHelperBuilder->getClientHelper(
            new ClientHelper()
        ));
    }

    public function testGetToolsHelper()
    {
        $this->assertInstanceOf(ToolsHelper::class, $this->apiHelperBuilder->getToolsHelper());
        $this->assertInstanceOf(ToolsHelper::class, $this->apiHelperBuilder->getToolsHelper(
            \Mockery::mock(ToolsHelper::class)
        ));
    }

    public function testGetConfigurationHelper()
    {
        $this->assertInstanceOf(ConfigurationHelper::class, $this->apiHelperBuilder->getConfigurationHelper());
        $this->assertInstanceOf(ConfigurationHelper::class, $this->apiHelperBuilder->getConfigurationHelper(
            \Mockery::mock(ConfigurationHelper::class)
        ));
    }

    public function testGetAdminInsuranceHelper()
    {
        $this->assertInstanceOf(AdminInsuranceHelper::class, $this->apiHelperBuilder->getAdminInsuranceHelper());
        $this->assertInstanceOf(AdminInsuranceHelper::class, $this->apiHelperBuilder->getAdminInsuranceHelper(
            \Mockery::mock(AdminInsuranceHelper::class)
        ));
    }
}
