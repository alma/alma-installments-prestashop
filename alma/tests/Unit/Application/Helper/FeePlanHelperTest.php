<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Helper;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;

class FeePlanHelperTest extends TestCase
{
    public function testGetPlanFromPlanKey()
    {
        $plan = [
            'kind' => 'general',
            'installments_count' => 1,
            'deferred_days' => 0,
            'deferred_months' => 0,
        ];
        $this->assertEquals($plan, FeePlanHelper::getPlanFromPlanKey('general_1_0_0'));
    }

    public function testGetPlanFromPlanKeyWithWrongPlanKey()
    {
        $plan = [
            'kind' => 'general',
            'installments_count' => 0,
            'deferred_days' => 0,
            'deferred_months' => 0,
        ];
        $this->assertEquals($plan, FeePlanHelper::getPlanFromPlanKey('toto'));
    }
}
