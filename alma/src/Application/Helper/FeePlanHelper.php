<?php

namespace PrestaShop\Module\Alma\Application\Helper;

class FeePlanHelper
{
    public static function getPlanFromPlanKey(string $string): array
    {
        $defaultPlan = [
            'kind' => 'general',
            'installments_count' => 0,
            'deferred_days' => 0,
            'deferred_months' => 0,
        ];

        if (!preg_match('/^([a-zA-Z]+)_(\d+)_(\d+)_(\d+)$/', $string, $matches)) {
            return $defaultPlan;
        }

        return [
            'kind' => $matches[1],
            'installments_count' => (int) $matches[2],
            'deferred_days' => (int) $matches[3],
            'deferred_months' => (int) $matches[4],
        ];
    }
}
