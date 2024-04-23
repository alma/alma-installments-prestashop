<?php
/**
 * 2018-2023 Alma SAS.
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\OrderHelper;
use Alma\PrestaShop\Helpers\PlanHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use PHPUnit\Framework\TestCase;

class PlanHelperTest extends TestCase
{
    protected function setUp()
    {
        $this->dateHelper = \Mockery::mock(DateHelper::class);
        $this->settingsHelper = \Mockery::mock(SettingsHelper::class);
        $this->customFieldsHelper = \Mockery::mock(CustomFieldsHelper::class);
        $this->context = \Mockery::mock(\Context::class);
        $this->translationHelper = \Mockery::mock(TranslationHelperTest::class);
        $this->module = \Mockery::mock(\Alma::class);

        $this->planHelper = new PlanHelper(
            $this->dateHelper,
            $this->settingsHelper,
            $this->customFieldsHelper,
            $this->context,
            $this->translationHelper,
            $this->module
        );
    }

    /**
     * @dataProvider provideIsPnxPlus4
     * @return void
     */
    public function testIsPnxPlus4($expected, $installmentsCount)
    {
        $plan = new FeePlan(
            [
                'installmentsCount' => $installmentsCount,
            ]
        );

        $this->assertEquals($expected, $this->planHelper->isPnxPlus4($plan));

    }

    public function provideIsPnxPlus4()
    {
        return [
            'test 4 installments' => [
                'expected' => false,
                'installmentsCount' => 3,
            ],
            'test 2 installments' => [
                'expected' => false,
                'installmentsCount' => 4,
            ],
            'test 6 installments' => [
                'expected' => true,
                'installmentsCount' => 6,
            ],
        ];
    }
    /**
     * @dataProvider provideIsDeferred
     *
     * @return void
     */
    public function testIsDeferred($expected, $deferredDays, $deferredMonths, $keyDeferredDays, $keyDeferredMonths)
    {
        $plan = new FeePlan(
            [
                $keyDeferredDays => $deferredDays,
                $keyDeferredMonths => $deferredMonths,
            ]
        );

        $this->assertEquals($expected, $this->planHelper->isDeferred($plan));

    }

    public function provideIsDeferred()
    {
        return [
            'test 1' => [
                'expected' => true,
                'deferredDays' => 1,
                'deferredMonths' => 1,
                'keyDeferredDays' => 'deferredDays',
                'keyDeferredMonths' => 'deferredMonths',
            ],
            'test 2' => [
                'expected' => true,
                'deferredDays' => 1,
                'deferredMonths' => 0,
                'keyDeferredDays' => 'deferredDays',
                'keyDeferredMonths' => 'deferredMonths',
            ],
            'test 3' => [
                'expected' => true,
                'deferredDays' => 0,
                'deferredMonths' => 1,
                'keyDeferredDays' => 'deferredDays',
                'keyDeferredMonths' => 'deferredMonths',
            ],
            'test 4' => [
                'expected' => false,
                'deferredDays' => 0,
                'deferredMonths' => 0,
                'keyDeferredDays' => 'deferredDays',
                'keyDeferredMonths' => 'deferredMonths',
            ],
            'test 5' => [
                'expected' => true,
                'deferredDays' => 1,
                'deferredMonths' => 1,
                'keyDeferredDays' => 'deferred_days',
                'keyDeferredMonths' => 'deferred_months',
            ],
            'test 6' => [
                'expected' => true,
                'deferredDays' => 1,
                'deferredMonths' => 0,
                'keyDeferredDays' => 'deferred_days',
                'keyDeferredMonths' => 'deferred_months',
            ],
            'test 7' => [
                'expected' => true,
                'deferredDays' => 0,
                'deferredMonths' => 1,
                'keyDeferredDays' => 'deferred_days',
                'keyDeferredMonths' => 'deferred_months',
            ],
            'test 8' => [
                'expected' => false,
                'deferredDays' => 0,
                'deferredMonths' => 0,
                'keyDeferredDays' => 'deferred_days',
                'keyDeferredMonths' => 'deferred_months',
            ],
        ];
    }
}
