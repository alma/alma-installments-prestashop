<?php

namespace PrestaShop\Module\Alma\Application\Presenter;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShopBundle\Translation\TranslatorInterface;

class FeePlanPresenter
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getTitle(FeePlan $feePlan): string
    {
        $title = $this->translator->trans('Pay Now', [], 'Modules.Alma.Settings');

        if ($feePlan->isPnXOnly() || $feePlan->isCredit()) {
            $title = $this->translator->trans('%d-installment payments', [], 'Modules.Alma.Settings');
            $title = sprintf($title, $feePlan->getInstallmentsCount());
        }
        if ($feePlan->isPayLaterOnly()) {
            $title = $this->translator->trans('Deferred payments + %d days', [], 'Modules.Alma.Settings');
            $title = sprintf($title, $feePlan->getDeferredDays());
        }

        return $title;
    }

    public function getLabel(FeePlan $feePlan): string
    {
        $title = $this->translator->trans('Enable pay now', [], 'Modules.Alma.Settings');

        if ($feePlan->isPnXOnly() || $feePlan->isCredit()) {
            $title = $this->translator->trans('Enable %d-installment payments', [], 'Modules.Alma.Settings');
            $title = sprintf($title, $feePlan->getInstallmentsCount());
        }
        if ($feePlan->isPayLaterOnly()) {
            $title = $this->translator->trans('Enable deferred payments + %d days', [], 'Modules.Alma.Settings');
            $title = sprintf($title, $feePlan->getDeferredDays());
        }

        return $title;
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function checkLimitAmountPlan(FeePlan $feePlan, int $minAmount, int $maxAmount): void
    {
        if ($minAmount < $feePlan->getMinPurchaseAmount()) {
            $message = $this->translator->trans('The minimum purchase amount cannot be lower than the minimum allowed by Alma.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }

        if ($maxAmount > $feePlan->getMaxPurchaseAmount()) {
            $message = $this->translator->trans('The maximum purchase amount cannot be higher than the maximum allowed by Alma.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }

        if ($minAmount > $feePlan->getMaxPurchaseAmount()) {
            $message = $this->translator->trans('The minimum purchase amount cannot be higher than the maximum.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }

        if ($maxAmount < $feePlan->getMinPurchaseAmount()) {
            $message = $this->translator->trans('The maximum purchase amount cannot be lower than the minimum.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }
    }
}
