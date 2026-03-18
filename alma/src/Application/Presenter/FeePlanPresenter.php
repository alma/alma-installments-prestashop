<?php

namespace PrestaShop\Module\Alma\Application\Presenter;

use Alma\Client\Domain\Entity\FeePlan;
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
}
