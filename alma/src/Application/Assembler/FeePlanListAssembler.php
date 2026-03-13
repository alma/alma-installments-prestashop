<?php

namespace PrestaShop\Module\Alma\Application\Assembler;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FeePlanListAssembler
{
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        FeePlansProvider $feePlansProvider,
        ConfigurationRepository $configurationRepository
    ) {
        $this->feePlansProvider = $feePlansProvider;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Get the fee plan list with the fee plan list from fee plan provider and the configuration values for each fee plan
     * @return array
     */
    public function getFeePlanList(): array
    {
        return $this->assemble();
    }

    /**
     * Assemble the fee plan list with the fee plan list from fee plan provider and the configuration values for each fee plan
     * @return array
     */
    public function assemble(): array
    {
        $feePlanList = $this->feePlansProvider->getFeePlanList();
        $feePlanConfiguration = $this->feePlansProvider->getFeePlanFromConfiguration();
        $feePlanListAssembled = [];
        /** @var FeePlan $feePlan */
        // TODO : Can we add optional fields (enabled and sortOrder) in the FeePlan entity to keep object FeePlan
        foreach ($feePlanList as $feePlan) {
            $planKey = $feePlan->getPlanKey();
            $feePlanListAssembled[] = [
                'allowed' => $feePlan->isAllowed(),
                'available_online' => $feePlan->isAvailableOnline(),
                'customer_fee_variable' => $feePlan->getCustomerFeeVariable(),
                'deferred_days' => $feePlan->getDeferredDays(),
                'deferred_months' => $feePlan->getDeferredMonths(),
                'installments_count' => $feePlan->getInstallmentsCount(),
                'kind' => $feePlan->getKind(),
                'max_purchase_amount' => $feePlan->getMaxPurchaseAmount(),
                'merchant_fee_variable' => $feePlan->getMerchantFeeVariable(),
                'merchant_fee_fixed' => $feePlan->getMerchantFeeFixed(),
                'min_purchase_amount' => $feePlan->getMinPurchaseAmount(),
                'enabled' => isset($feePlanConfiguration[$planKey]) && (bool) $feePlanConfiguration[$planKey]['state'],
                'sort_order' => isset($feePlanConfiguration[$planKey]) ? (int) $feePlanConfiguration[$planKey]['sort_order'] : 0,
            ];
        }
        return $feePlanListAssembled;
    }
}
