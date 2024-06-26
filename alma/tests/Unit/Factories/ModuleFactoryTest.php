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

namespace Alma\PrestaShop\Tests\Unit\Factories;

use Alma\PrestaShop\Builders\Factories\ModuleFactoryBuilder;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\ToolsHelper;
use PHPUnit\Framework\TestCase;

class ModuleFactoryTest extends TestCase
{
    /**
     * @var ModuleFactory
     */
    protected $moduleFactory;

    /**
     * @var ModuleFactoryBuilder
     */
    protected $moduleFactoryBuilder;

    /**
     * @var ToolsHelper
     */
    protected $toolsHelperMock;

    public function setUp()
    {
        $this->moduleFactoryBuilder = new ModuleFactoryBuilder();
        $this->moduleFactory = $this->moduleFactoryBuilder->getInstance();
        $this->toolsHelperMock = $this->createMock(ToolsHelper::class);
    }

    public function tearDown()
    {
        \Mockery::close();
        $this->moduleFactory = null;
        $this->moduleFactoryBuilder = null;
        $this->toolsHelperMock = null;
    }

    public function testGetModule()
    {
        $this->assertInstanceOf(\Module::class, $this->moduleFactory->getModule());
    }

    public function testGetModuleNameNoModule()
    {
        $moduleFactoryMock = \Mockery::mock(ModuleFactory::class)->makePartial();
        $moduleFactoryMock->shouldReceive('getModule')->andReturn(false);
        $this->assertEquals('', $moduleFactoryMock->getModuleName());
    }

    public function testGetModuleName()
    {
        $moduleFactoryMock = \Mockery::mock(ModuleFactory::class)->makePartial();

        $moduleMock = $this->createMock(\Module::class);
        $moduleMock->name = 'module name';
        $moduleFactoryMock->shouldReceive('getModule')->andReturn($moduleMock);

        $this->assertEquals($moduleMock->name, $moduleFactoryMock->getModuleName());
    }

    public function testL()
    {
        $this->assertEquals('My wording to translate', $this->moduleFactory->l('My wording to translate', 'ClassNameTest'));
    }

    public function testIsInstalledPsAfter17()
    {
        $this->toolsHelperMock->expects($this->once())
            ->method('psVersionCompare')
            ->willReturn(false);

        $moduleFactory = \Mockery::mock(ModuleFactory::class, [$this->toolsHelperMock])->makePartial();

        $moduleFactory->shouldReceive('isInstalledAfter17')->once();
        $moduleFactory->shouldNotReceive('isInstalledBefore17');
        $moduleFactory->isInstalled('alma');
    }

    public function testIsInstalledPsBefore17()
    {
        $this->toolsHelperMock->expects($this->once())
            ->method('psVersionCompare')
            ->willReturn(true);

        $moduleFactory = \Mockery::mock(ModuleFactory::class, [$this->toolsHelperMock])->makePartial();

        $moduleFactory->shouldReceive('isInstalledBefore17');
        $moduleFactory->shouldNotReceive('isInstalledAfter17');
        $moduleFactory->isInstalled('fakename');
    }
}
