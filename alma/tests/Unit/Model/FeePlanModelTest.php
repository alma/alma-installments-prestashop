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

use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Model\FeePlanModel;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
use Alma\PrestaShop\Tests\Unit\Helper\FeePlansDataProvider;
use PHPUnit\Framework\TestCase;

class FeePlanModelTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    protected $settingsHelperMock;
    /**
     * @var \Alma\PrestaShop\Helpers\PriceHelper
     */
    protected $priceHelperMock;
    /**
     * @var \Alma\PrestaShop\Tests\Unit\Helper\FeePlansDataProvider
     */
    protected $feePlansDataProvider;
    /**
     * @var \Alma\PrestaShop\Model\FeePlanModel
     */
    protected $feePlanModel;
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    protected $configurationProxyMock;

    public function setUp()
    {
        $this->settingsHelperMock = $this->createMock(SettingsHelper::class);
        $this->priceHelperMock = $this->createMock(PriceHelper::class);
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);
        $this->configurationProxyMock = $this->createMock(ConfigurationProxy::class);
        $this->feePlanModel = new FeePlanModel(
            $this->settingsHelperMock,
            $this->priceHelperMock,
            $this->toolsProxyMock,
            $this->configurationProxyMock
        );
        $this->feePlansDataProvider = new FeePlansDataProvider();
    }

    /**
     * @return void
     */
    public function testGetFeePlansOrderedWithDeferredInLast()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $expected = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('isDeferred')
            ->withConsecutive(
                [$this->feePlansDataProvider->planPayNow()],
                [$this->feePlansDataProvider->planP2x()],
                [$this->feePlansDataProvider->planP3x()],
                [$this->feePlansDataProvider->planP4x()],
                [$this->feePlansDataProvider->planDeferred15()]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                false,
                false,
                true
            );
        $this->settingsHelperMock->expects($this->once())
            ->method('getDuration')
            ->with($this->feePlansDataProvider->planDeferred15())
            ->willReturn(15);
        $this->assertEquals($expected, $this->feePlanModel->getFeePlansOrdered($feePlans)
        );
    }

    /**
     * @return void
     */
    public function testGetFeePlansOrderedWithDeferredInFirst()
    {
        $feePlans = [
            $this->feePlansDataProvider->planDeferred15(),
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
        ];
        $expected = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('isDeferred')
            ->withConsecutive(
                [$this->feePlansDataProvider->planDeferred15()],
                [$this->feePlansDataProvider->planPayNow()],
                [$this->feePlansDataProvider->planP2x()],
                [$this->feePlansDataProvider->planP3x()],
                [$this->feePlansDataProvider->planP4x()]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false,
                false,
                false,
                false
            );
        $this->settingsHelperMock->expects($this->once())
            ->method('getDuration')
            ->with($this->feePlansDataProvider->planDeferred15())
            ->willReturn(15);
        $this->assertEquals($expected, $this->feePlanModel->getFeePlansOrdered($feePlans)
        );
    }

    /**
     * @return void
     */
    public function testGetFieldsValueFromFeePlansWithEmptyFeePlan()
    {
        $this->assertEquals([], $this->feePlanModel->getFieldsValueFromFeePlans([]));
    }

    /**
     * @return void
     */
    public function testGetFieldsValueFromFeePlans()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $expected = [
            'ALMA_general_1_0_0_ENABLED_ON' => 1,
            'ALMA_general_1_0_0_MIN_AMOUNT' => 1,
            'ALMA_general_1_0_0_MAX_AMOUNT' => 2000,
            'ALMA_general_1_0_0_SORT_ORDER' => 1,
            'ALMA_general_2_0_0_ENABLED_ON' => 1,
            'ALMA_general_2_0_0_MIN_AMOUNT' => 50,
            'ALMA_general_2_0_0_MAX_AMOUNT' => 2000,
            'ALMA_general_2_0_0_SORT_ORDER' => 2,
            'ALMA_general_3_0_0_ENABLED_ON' => 1,
            'ALMA_general_3_0_0_MIN_AMOUNT' => 50,
            'ALMA_general_3_0_0_MAX_AMOUNT' => 2000,
            'ALMA_general_3_0_0_SORT_ORDER' => 3,
            'ALMA_general_4_0_0_ENABLED_ON' => 1,
            'ALMA_general_4_0_0_MIN_AMOUNT' => 50,
            'ALMA_general_4_0_0_MAX_AMOUNT' => 2000,
            'ALMA_general_4_0_0_SORT_ORDER' => 4,
            'ALMA_general_1_15_0_ENABLED_ON' => 1,
            'ALMA_general_1_15_0_MIN_AMOUNT' => 50,
            'ALMA_general_1_15_0_MAX_AMOUNT' => 2000,
            'ALMA_general_1_15_0_SORT_ORDER' => 5,
        ];
        $this->configurationProxyMock->expects($this->once())
            ->method('get')
            ->with('ALMA_FEE_PLANS')
            ->willReturn('{
                  "general_1_0_0": {
                    "enabled": 1,
                    "min": 100,
                    "max": 200000,
                    "deferred_trigger_limit_days": null,
                    "order": 1
                  },
                  "general_2_0_0": {
                    "enabled": 1,
                    "min": 5000,
                    "max": 200000,
                    "deferred_trigger_limit_days": null,
                    "order": 2
                  },
                  "general_3_0_0": {
                    "enabled": 1,
                    "min": 5000,
                    "max": 200000,
                    "deferred_trigger_limit_days": null,
                    "order": 3
                  },
                  "general_4_0_0": {
                    "enabled": 1,
                    "min": 5000,
                    "max": 200000,
                    "deferred_trigger_limit_days": null,
                    "order": 4
                  },
                 "general_1_15_0": {
                    "enabled": 1,
                    "min": 5000,
                    "max": 200000,
                    "deferred_trigger_limit_days": null,
                    "order": 5
                  }
                }');
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('keyForFeePlan')
            ->withConsecutive(
                [$this->feePlansDataProvider->planPayNow()],
                [$this->feePlansDataProvider->planP2x()],
                [$this->feePlansDataProvider->planP3x()],
                [$this->feePlansDataProvider->planP4x()],
                [$this->feePlansDataProvider->planDeferred15()]
            )
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0',
                'general_3_0_0',
                'general_4_0_0',
                'general_1_15_0'
            );
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceFromCents')
            ->withConsecutive(
                [100],
                [200000],
                [5000],
                [200000],
                [5000],
                [200000],
                [5000],
                [200000],
                [5000],
                [200000]
            )
            ->willReturnOnConsecutiveCalls(
                1,
                2000,
                50,
                2000,
                50,
                2000,
                50,
                2000,
                50,
                2000
            );

        $this->assertEquals($expected, $this->feePlanModel->getFieldsValueFromFeePlans($feePlans));
    }

    /**
     * @return void
     */
    public function testGetFeePlanForSaveWithEmptyFeePlan()
    {
        $this->assertEquals([], $this->feePlanModel->getFeePlanForSave([]));
    }

    /**
     * @return void
     */
    public function testGetFeePlanForSave()
    {
        $feePlans = [
            $this->feePlansDataProvider->planPayNow(),
            $this->feePlansDataProvider->planP2x(),
            $this->feePlansDataProvider->planP3x(),
            $this->feePlansDataProvider->planP4x(),
            $this->feePlansDataProvider->planDeferred15(),
        ];
        $expected = [
            'general_1_0_0' => [
                'enabled' => '1',
                'min' => 100,
                'max' => 200000,
                'order' => 1,
                'deferred_trigger_limit_days' => null,
            ],
            'general_2_0_0' => [
                'enabled' => '1',
                'min' => 5000,
                'max' => 200000,
                'order' => 2,
                'deferred_trigger_limit_days' => null,
            ],
            'general_3_0_0' => [
                'enabled' => '1',
                'min' => 5000,
                'max' => 200000,
                'order' => 3,
                'deferred_trigger_limit_days' => null,
            ],
            'general_4_0_0' => [
                'enabled' => '1',
                'min' => 5000,
                'max' => 200000,
                'order' => 4,
                'deferred_trigger_limit_days' => null,
            ],
            'general_1_15_0' => [
                'enabled' => '1',
                'min' => 5000,
                'max' => 200000,
                'order' => 5,
                'deferred_trigger_limit_days' => null,
            ],
        ];
        $this->settingsHelperMock->expects($this->exactly(5))
            ->method('keyForFeePlan')
            ->withConsecutive(
                [$this->feePlansDataProvider->planPayNow()],
                [$this->feePlansDataProvider->planP2x()],
                [$this->feePlansDataProvider->planP3x()],
                [$this->feePlansDataProvider->planP4x()],
                [$this->feePlansDataProvider->planDeferred15()]
            )
            ->willReturnOnConsecutiveCalls(
                'general_1_0_0',
                'general_2_0_0',
                'general_3_0_0',
                'general_4_0_0',
                'general_1_15_0'
            );
        $this->settingsHelperMock->expects($this->exactly(3))
            ->method('isDeferred')
            ->withConsecutive(
                [$this->feePlansDataProvider->planP2x()],
                [$this->feePlansDataProvider->planP3x()],
                [$this->feePlansDataProvider->planP4x()],
                [$this->feePlansDataProvider->planDeferred15()]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                false,
                true
            );
        $this->toolsProxyMock->expects($this->exactly(25))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_general_1_0_0_MIN_AMOUNT'],
                ['ALMA_general_1_0_0_MAX_AMOUNT'],
                ['ALMA_general_1_0_0_SORT_ORDER'],
                ['ALMA_general_1_0_0_ENABLED_ON'],
                ['ALMA_general_1_0_0_SORT_ORDER'],
                ['ALMA_general_2_0_0_MIN_AMOUNT'],
                ['ALMA_general_2_0_0_MAX_AMOUNT'],
                ['ALMA_general_2_0_0_SORT_ORDER'],
                ['ALMA_general_2_0_0_ENABLED_ON'],
                ['ALMA_general_2_0_0_SORT_ORDER'],
                ['ALMA_general_3_0_0_MIN_AMOUNT'],
                ['ALMA_general_3_0_0_MAX_AMOUNT'],
                ['ALMA_general_3_0_0_SORT_ORDER'],
                ['ALMA_general_3_0_0_ENABLED_ON'],
                ['ALMA_general_3_0_0_SORT_ORDER'],
                ['ALMA_general_4_0_0_MIN_AMOUNT'],
                ['ALMA_general_4_0_0_MAX_AMOUNT'],
                ['ALMA_general_4_0_0_SORT_ORDER'],
                ['ALMA_general_4_0_0_ENABLED_ON'],
                ['ALMA_general_4_0_0_SORT_ORDER'],
                ['ALMA_general_1_15_0_MIN_AMOUNT'],
                ['ALMA_general_1_15_0_MAX_AMOUNT'],
                ['ALMA_general_1_15_0_SORT_ORDER'],
                ['ALMA_general_1_15_0_ENABLED_ON'],
                ['ALMA_general_1_15_0_SORT_ORDER']
            )
            ->willReturnOnConsecutiveCalls(
                1,
                2000,
                1,
                1,
                1,
                50,
                2000,
                2,
                1,
                2,
                50,
                2000,
                3,
                1,
                3,
                50,
                2000,
                4,
                1,
                4,
                50,
                2000,
                5,
                1,
                5
            );
        $this->priceHelperMock->expects($this->exactly(10))
            ->method('convertPriceToCents')
            ->withConsecutive(
                [1],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [2000],
                [50],
                [2000]
            )
            ->willReturnOnConsecutiveCalls(
                100,
                200000,
                5000,
                200000,
                5000,
                200000,
                5000,
                200000,
                5000,
                200000
            );

        $this->assertEquals($expected, $this->feePlanModel->getFeePlanForSave($feePlans));
    }
}
