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
            $title = $this->translator->trans('%installmentCount%-installment payments', ['%installmentCount%' => $feePlan->getInstallmentsCount()], 'Modules.Alma.Settings');
        }
        if ($feePlan->isPayLaterOnly()) {
            $title = $this->translator->trans('Deferred payments + %deferredDay% days', ['%deferredDay%' => $feePlan->getDeferredDays()], 'Modules.Alma.Settings');
        }

        return $title;
    }

    public function getLabel(FeePlan $feePlan): string
    {
        $title = $this->translator->trans('Enable pay now', [], 'Modules.Alma.Settings');

        if ($feePlan->isPnXOnly() || $feePlan->isCredit()) {
            $title = $this->translator->trans('Enable %installmentCount%-installment payments', ['%installmentCount%' => $feePlan->getInstallmentsCount()], 'Modules.Alma.Settings');
        }
        if ($feePlan->isPayLaterOnly()) {
            $title = $this->translator->trans('Enable deferred payments + %deferredDay% days', ['%deferredDay%' => $feePlan->getDeferredDays()], 'Modules.Alma.Settings');
        }

        return $title;
    }
}
