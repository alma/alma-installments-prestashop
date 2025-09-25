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

namespace Alma\PrestaShop\Tests\Unit\Model;

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Model\AlmaModuleModel;
use Alma\PrestaShop\Proxy\ModuleProxy;
use PHPUnit\Framework\TestCase;

class AlmaModuleModelTest extends TestCase
{
    /**
     * @var AlmaModuleModel
     */
    protected $almaModuleModel;

    /**
     * @var ModuleProxy
     */
    protected $moduleProxyMock;
    /**
     * @var \Db
     */
    protected $dbMock;

    /**
     * @var \Module
     */
    protected $moduleMock;

    public function setUp()
    {
        $this->moduleMock = $this->createMock(\Module::class);
        $this->moduleProxyMock = $this->createMock(ModuleProxy::class);
        $this->dbMock = $this->createMock(\Db::class);
        $this->almaModuleModel = new AlmaModuleModel(
            $this->moduleProxyMock,
            $this->dbMock
        );
    }

    public function tearDown()
    {
        $this->moduleProxyMock = null;
        $this->dbMock = null;
        $this->almaModuleModel = null;
    }

    /**
     * @dataProvider moduleDataProvider
     *
     * @return void
     */
    public function testGetAlmaModuleVersion($module, $expectedVersion)
    {
        if ($module) {
            $module->version = $expectedVersion;
            $this->moduleProxyMock->expects($this->once())
                ->method('getModuleVersion')
                ->with($module)
                ->willReturn($expectedVersion);
        }

        $this->moduleProxyMock->expects($this->once())
            ->method('getModule')
            ->with(ConstantsHelper::ALMA_MODULE_NAME)
            ->willReturn($module);

        $this->assertEquals($expectedVersion, $this->almaModuleModel->getVersion());
    }

    public function moduleDataProvider()
    {
        return [
            'With module' => [
                'module' => $this->createMock(\Module::class),
                'expectedVersion' => '4.4.0',
            ],
            'Without module' => [
                'module' => false,
                'expectedVersion' => '',
            ],
        ];
    }

    /**
     * @dataProvider getPositionDataProvider
     *
     * @return void
     */
    public function testGetPosition($hookModule, $expected)
    {
        $this->dbMock->method('getRow')
            ->willReturn($hookModule);

        $this->assertEquals($expected, $this->almaModuleModel->getPosition());
    }

    /**
     * @dataProvider getPaymentMethodsListDataProvider
     *
     * @return void
     */
    public function testGetPaymentMethodList($sqlReturn, $expected)
    {
        $this->dbMock->method('executeS')
            ->willReturn($sqlReturn);

        $this->assertEquals($expected, $this->almaModuleModel->getPaymentMethodsList());
    }

    /**
     * @return array[]
     */
    public function getPositionDataProvider()
    {
        return [
            'With SQL return position' => [
                'hookModule' => ['position' => 3],
                'expected' => '3',
            ],
            'Without SQL return position' => [
                'hookModule' => false,
                'expected' => '',
            ],
        ];
    }

    public function getPaymentMethodsListDataProvider()
    {
        return [
            'With SQL return list' => [
                'sqlReturn' => [
                    ['name' => 'ps_checkout',  'position' => 1],
                    ['name' => 'ps_checkpayment',  'position' => 2],
                    ['name' => 'ps_wirepayment',  'position' => 3],
                    ['name' => 'alma',  'position' => 4]
                ],
                'expected' => [
                    ['name' => 'ps_checkout',  'position' => 1],
                    ['name' => 'ps_checkpayment',  'position' => 2],
                    ['name' => 'ps_wirepayment',  'position' => 3],
                    ['name' => 'alma',  'position' => 4]
                ],
            ],
            'Without SQL return list' => [
                'sqlReturn' => [],
                'expected' => [],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetModule()
    {
        $this->moduleProxyMock->expects($this->once())
            ->method('getModule')
            ->with(ConstantsHelper::ALMA_MODULE_NAME)
            ->willReturn($this->moduleMock);
        $this->almaModuleModel->getModule();
    }
}
