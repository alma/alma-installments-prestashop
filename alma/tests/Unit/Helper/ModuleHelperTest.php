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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\PrestaShop\Helpers\ModuleHelper;
use Alma\PrestaShop\Proxy\ModuleProxy;
use PHPUnit\Framework\TestCase;

class ModuleHelperTest extends TestCase
{
    /**
     * @var ModuleHelper
     */
    protected $moduleHelper;
    /**
     * @var ModuleProxy
     */
    protected $moduleProxyMock;

    public function setUp()
    {
        $this->moduleProxyMock = $this->createMock(ModuleProxy::class);
        $this->moduleHelper = new ModuleHelper(
            $this->moduleProxyMock
        );
    }

    /**
     * @dataProvider modulesInstalledDataProvider
     *
     * @return void
     */
    public function testGetModuleListWithListOfModules($moduleInstalled, $expectedModulesList)
    {
        $this->moduleProxyMock->expects($this->once())
            ->method('getModulesInstalled')
            ->willReturn($moduleInstalled);
        $this->assertEquals($expectedModulesList, $this->moduleHelper->getModuleList());
    }

    public function modulesInstalledDataProvider()
    {
        return [
            'With Modules installed' => [
                'Modules installed' => [
                    [
                        'id_module' => '1',
                        'name' => 'blockwishlist',
                        'active' => '1',
                        'version' => '2.1.0',
                    ],
                    [
                        'id_module' => '2',
                        'name' => 'contactform',
                        'active' => '1',
                        'version' => '4.3.0',
                    ],
                    [
                        'id_module' => '3',
                        'name' => 'dashactivity',
                        'active' => '1',
                        'version' => '2.0.2',
                    ],
                ],
                'Modules list' => [
                    [
                        'name' => 'blockwishlist',
                        'version' => '2.1.0',
                    ],
                    [
                        'name' => 'contactform',
                        'version' => '4.3.0',
                    ],
                    [
                        'name' => 'dashactivity',
                        'version' => '2.0.2',
                    ],
                ],
            ],
            'With no module installed' => [
                'Modules installed' => [],
                'Modules list' => [],
            ],
        ];
    }
}
