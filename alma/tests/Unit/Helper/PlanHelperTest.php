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
    public function testIsPnxPlus4()
    {
        $plan = new FeePlan(
            [
                'installmentsCount' => 4,
            ]
        );

        $this->assertEquals($this->planHelper->isPnxPlus4($plan), false);

        $plan = new FeePlan(
            [
                'installmentsCount' => 3,
            ]
        );

        $this->assertEquals($this->planHelper->isPnxPlus4($plan), false);

        $plan = new FeePlan(
            [
                'installmentsCount' => 6,
            ]
        );

        $this->assertEquals($this->planHelper->isPnxPlus4($plan), true);
    }

    public function testIsDeferred()
    {
        $plan = new FeePlan(
            [
                'deferred_days' => 0,
                'deferred_months' => 0,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), false);

        $plan = new FeePlan(
            [
                'deferred_days' => 0,
                'deferred_months' => 1,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), true);


        $plan = new FeePlan(
            [
                'deferred_days' => 1,
                'deferred_months' => 0,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), true);

        $plan = new FeePlan(
            [
                'deferred_days' => 1,
                'deferred_months' => 1,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), true);

        $plan = new FeePlan(
            [
                'deferredDays' => 0,
                'deferredMonths' => 0,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), false);

        $plan = new FeePlan(
            [
                'deferredDays' => 0,
                'deferredMonths' => 1,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), true);


        $plan = new FeePlan(
            [
                'deferredDays' => 1,
                'deferredMonths' => 0,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), true);

        $plan = new FeePlan(
            [
                'deferredDays' => 1,
                'deferredMonths' => 1,
            ]
        );

        $this->assertEquals($this->planHelper->isDeferred($plan), true);

    }
}
