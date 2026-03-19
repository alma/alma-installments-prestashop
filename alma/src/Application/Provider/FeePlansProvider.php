<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Application\Exception\ParametersException;
use Alma\Client\Domain\Entity\FeePlan;
use Alma\Client\Domain\Entity\FeePlanList;
use Alma\Client\Domain\ValueObject\PaymentMethod;
use Alma\Plugin\Application\Port\FeePlanProviderInterface;
use Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FeePlansProvider implements FeePlanProviderInterface
{
    /**
     * @var \Alma\Client\Application\Endpoint\MerchantEndpoint
     */
    private MerchantEndpoint $merchantEndpoint;
    /**
     * @var \Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface
     */
    private FeePlanListInterface $feePlanList;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        MerchantEndpoint $merchantEndpoint,
        ConfigurationRepository $configurationRepository
    ) {
        $this->merchantEndpoint = $merchantEndpoint;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * @param bool $forceRefresh
     * @return \Alma\Client\Domain\Entity\FeePlanList
     */
    public function getFeePlanList(bool $forceRefresh = false): FeePlanList
    {
        if (!isset($this->feePlanList) || $forceRefresh) {
            $this->feePlanList = $this->getFeePlansAllowed();
        }

        return $this->feePlanList;
    }

    /**
     * Get the fee plan list from configuration
     * @return array
     */
    public function getFeePlanFromConfiguration(): array
    {
        $feePlanList = $this->configurationRepository->get(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST);

        if (empty($feePlanList)) {
            return [];
        }

        return json_decode($feePlanList, true);
    }

    public function getOriginalFeePlan(): FeePlanList
    {
        $originalFeePlanList = $this->configurationRepository->get(FeePlansAdminForm::KEY_FIELD_ORIGINAL_FEE_PLAN);

        $feePlanList = new FeePlanList();

        if (!empty($originalFeePlanList)) {
            foreach (json_decode($originalFeePlanList, true) as $feePlan) {
                try {
                    $feePlanList->add(new FeePlan($feePlan));
                } catch (ParametersException $e) {
                    // TODO : Add Log here
                }
            }
        }

        return $feePlanList;
    }

    /**
     * @return \Alma\Plugin\Infrastructure\Adapter\FeePlanListInterface
     */
    private function getFeePlansAllowed(): FeePlanListInterface
    {
        try {
            $feePlanList = $this->merchantEndpoint->getFeePlanList(FeePlan::KIND_GENERAL, 'all', true)->filterAllowed();
        } catch (MerchantEndpointException $e) {
            // TODO : Add Log here
            $feePlanList = new FeePlanList();
        }

        return $this->orderFeePlanList($feePlanList);
    }

    /**
     * Order the fee plan list by payment method.
     * Pay Now, PNX, Credit, Deferred Plans.
     * TODO : I think we should move this logic to the FeePlanList class in the PHP-Client
     *
     * @param FeePlanList $feePlanList
     * @return FeePlanList
     */
    private function orderFeePlanList(FeePlanList $feePlanList): FeePlanList
    {
        $orderedFeePlanList = new FeePlanList();
        $orderedMethods = [
            PaymentMethod::PAY_NOW,
            PaymentMethod::PNX,
            PaymentMethod::CREDIT,
            PaymentMethod::PAY_LATER,
        ];

        foreach ($orderedMethods as $method) {
            foreach ($feePlanList->filterFeePlanList([$method]) as $plan) {
                $orderedFeePlanList->add($plan);
            }
        }

        return $orderedFeePlanList;
    }
}
