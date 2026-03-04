<?php

namespace PrestaShop\Module\Alma\Application\Helper;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;

class FeePlanHelper
{
    public static function getTitle(FeePlan $feePlan): string
    {
        $title = 'Pay Now';

        if ($feePlan->isPnXOnly() || $feePlan->isCredit()) {
            $title = sprintf('%d-installment payments', $feePlan->getInstallmentsCount());
        }
        if ($feePlan->isPayLaterOnly()) {
            $title = sprintf('Deferred payments + %d days', $feePlan->getDeferredDays());
        }

        return $title;
    }

    public static function getLabel(FeePlan $feePlan): string
    {
        $title = 'Enable pay now';

        if ($feePlan->isPnXOnly() || $feePlan->isCredit()) {
            $title = sprintf('Enable %d-installment payments', $feePlan->getInstallmentsCount());
        }
        if ($feePlan->isPayLaterOnly()) {
            $title = sprintf('Enable deferred payments +%d days', $feePlan->getDeferredDays());
        }

        return $title;
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public static function checkLimitAmountPlan(FeePlan $feePlan, int $minAmount, int $maxAmount): void
    {
        if ($minAmount < $feePlan->getMinPurchaseAmount()) {
            throw new FeePlansException('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        }

        if ($maxAmount > $feePlan->getMaxPurchaseAmount()) {
            throw new FeePlansException('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        }

        if ($minAmount > $feePlan->getMaxPurchaseAmount()) {
            throw new FeePlansException('The minimum purchase amount cannot be higher than the maximum.');
        }

        if ($maxAmount < $feePlan->getMinPurchaseAmount()) {
            throw new FeePlansException('The maximum purchase amount cannot be lower than the minimum.');
        }
    }
}
