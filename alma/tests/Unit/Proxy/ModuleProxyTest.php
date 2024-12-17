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

namespace Alma\PrestaShop\Tests\Unit\Proxy;

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Proxy\ModuleProxy;
use PHPUnit\Framework\TestCase;

class ModuleProxyTest extends TestCase
{
    /**
     * @var ModuleProxy
     */
    protected $moduleProxy;

    /**
     * @var ToolsHelper
     */
    protected $toolsHelperMock;

    public function setUp()
    {
        $this->toolsHelperMock = $this->createMock(ToolsHelper::class);
        $this->moduleProxy = new ModuleProxy(
            $this->toolsHelperMock
        );
    }

    public function tearDown()
    {
        $this->moduleProxy = null;
        $this->toolsHelperMock = null;
    }

    public function testGetModule()
    {
        $this->assertInstanceOf(\Module::class, $this->moduleProxy->getModule(ConstantsHelper::ALMA_MODULE_NAME));
    }

    public function testIsInstalledPsAfter17()
    {
        $this->toolsHelperMock->expects($this->once())
            ->method('psVersionCompare')
            ->willReturn(false);

        $moduleProxyPartialMock = $this->getMockBuilder(ModuleProxy::class)
            ->setConstructorArgs([$this->toolsHelperMock])
            ->setMethods(['isInstalledAfter17', 'isInstalledBefore17'])
            ->getMock();
        $moduleProxyPartialMock->expects($this->never())
            ->method('isInstalledBefore17');
        $moduleProxyPartialMock->expects($this->once())
            ->method('isInstalledAfter17')
            ->willReturn(true);

        $this->assertTrue($moduleProxyPartialMock->isInstalled('mymodulename'));
    }

    public function testIsInstalledPsBefore17()
    {
        $this->toolsHelperMock->expects($this->once())
            ->method('psVersionCompare')
            ->willReturn(true);

        $moduleProxyPartialMock = $this->getMockBuilder(ModuleProxy::class)
            ->setConstructorArgs([$this->toolsHelperMock])
            ->setMethods(['isInstalledBefore17', 'isInstalledAfter17'])
            ->getMock();
        $moduleProxyPartialMock->expects($this->never())
            ->method('isInstalledAfter17');
        $moduleProxyPartialMock->expects($this->once())
            ->method('isInstalledBefore17')
            ->willReturn(false);

        $this->assertFalse($moduleProxyPartialMock->isInstalled('mymodulename'));
    }

    public function testGetModuleVersion()
    {
        $module = $this->createMock(\Module::class);
        $module->version = '4.4.0';

        $this->assertEquals('4.4.0', $this->moduleProxy->getModuleVersion($module));
    }
}
