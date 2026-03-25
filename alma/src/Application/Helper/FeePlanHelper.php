<?php

namespace PrestaShop\Module\Alma\Application\Helper;

class FeePlanHelper
{
    public static function getPlanFromPlanKey(string $string): array
    {
        $parts = explode('_', $string);

        return [
            'kind' => $parts[0],
            'installments_count' => (int) $parts[1],
            'deferred_days' => (int) $parts[2],
            'deferred_months' => (int) $parts[3],
        ];
    }
}
