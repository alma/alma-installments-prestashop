<?php

namespace PrestaShop\Module\Alma\Application\Helper;

class FeePlanHelper
{
    public static function getTitle(int $installmentCount, int $deferredDays, int $deferredMonth): string
    {
        $title = 'Unknown';

        if ($installmentCount === 1 && $deferredDays === 0 && $deferredMonth === 0) {
            $title = 'Pay Now';
        }
        if ($installmentCount > 1 && ($deferredDays === 0 && $deferredMonth === 0)) {
            $title = sprintf('%d-installment payments', $installmentCount);
        }
        if ($installmentCount === 1 && ($deferredDays !== 0 && $deferredMonth === 0)) {
            $title = sprintf('Deferred payments + %d days', $deferredDays);
        }
        if ($installmentCount === 1 && ($deferredDays === 0 && $deferredMonth !== 0)) {
            $title = sprintf('Deferred payments + %d months', $deferredMonth);
        }

        return $title;
    }

    public static function getLabel(int $installmentCount, int $deferredDays, int $deferredMonth): string
    {
        $title = 'Unknown';

        if ($installmentCount === 1 && $deferredDays === 0 && $deferredMonth === 0) {
            $title = 'Enable pay now';
        }
        if ($installmentCount > 1 && ($deferredDays === 0 && $deferredMonth === 0)) {
            $title = sprintf('Enable %d-installment payments', $installmentCount);
        }
        if ($installmentCount === 1 && ($deferredDays !== 0 && $deferredMonth === 0)) {
            $title = sprintf('Enable deferred payments +%d days', $deferredDays);
        }
        if ($installmentCount === 1 && ($deferredDays === 0 && $deferredMonth !== 0)) {
            $title = sprintf('Enable deferred payments +%d months', $deferredMonth);
        }

        return $title;
    }
}
