<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

use Alma\Client\Domain\Entity\Eligibility;

final class EligibilityMock
{
    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public static function eligibility(int $installmentCount, int $deferredDays = 0, int $deferredMonths = 0): Eligibility
    {
        return new Eligibility([
            'eligible' => true,
            'installments_count' => $installmentCount,
            'deferred_days' => $deferredDays,
            'deferred_months' => $deferredMonths,
            'customer_fee' => 0,
            'customer_total_cost_amount' => 0,
            'customer_total_cost_bps' => 0,
            'payment_plan' => [],
            'annual_interest_rate' => 0,
        ]);
    }
}
