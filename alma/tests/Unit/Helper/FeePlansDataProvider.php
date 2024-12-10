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

use Alma\API\Entities\FeePlan;
use PHPUnit\Framework\TestCase;

class FeePlansDataProvider extends TestCase
{
    public function planPayNow()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 1;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 100;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 75;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    public function planP2x()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 2;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 310;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    public function planP3x()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 3;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 380;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    public function planP4x()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 4;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 480;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    public function planDeferred15()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 1;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 15;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 450;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }
}
