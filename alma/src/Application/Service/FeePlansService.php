<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;

class FeePlansService
{
    private \Context $context;
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlanProvider;

    public function __construct(
        \Context $context,
        FeePlansProvider $feePlanProvider
    ) {
        $this->context = $context;
        $this->feePlanProvider = $feePlanProvider;
    }

    public function createTemplateTabs()
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/fee_plans_tabs.tpl');
        $tpl->assign([
            'fee_plans' => $this->feePlansTabs(),
        ]);

        return $tpl->fetch();
    }

    /**
     * Get fee plans for loop the tabs in the fee plans template
     * @return array
     */
    public function feePlansTabs(): array
    {
        $feePlansTabs = [];
        $feePlansProvider = $this->feePlanProvider->getFeePlanList();
        foreach ($feePlansProvider as $feePlan) {
            /** @var FeePlan $feePlan */
            $planKey = $feePlan->getPlanKey();
            $feePlansTabs[$planKey] = [
                'title' => FeePlanPresenter::getTitle($feePlan),
                // TODO : Default active tab. We need to enable the first plan enable if saved in DB or P3X for the first save
                'active' => $planKey === 'general_3_0_0',
            ];
        }

        return $feePlansTabs;
    }

    public function feePlansFields(): array
    {
        $feePlansFields = [];
        $feePlansProvider = $this->feePlanProvider->getFeePlanList();

        foreach ($feePlansProvider as $feePlan) {
            /** @var FeePlan $feePlan */
            $planKey = mb_strtoupper($feePlan->getPlanKey());
            $planKeyTab = $feePlan->getPlanKey();
            $feePlansFields = array_merge($feePlansFields, [
                'ALMA_' . $planKey . '_STATE' => [
                    'type' => 'switch',
                    'label' => FeePlanPresenter::getLabel($feePlan),
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'values' => [
                            [
                                'id' => 'ENABLE',
                                'value' => 1,
                                'label' => 'Enabled',
                            ],
                            [
                                'id' => 'DISABLE',
                                'value' => 0,
                                'label' => 'Disabled'
                            ]
                        ],
                    ],
                ],
                'ALMA_' . $planKey . '_MIN_AMOUNT' => [
                    'type' => 'text',
                    'label' => 'Minimum amount (€)',
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'size' => 20,
                        'desc' => 'Minimum purchase amount to activate this plan',
                    ],
                ],
                'ALMA_' . $planKey . '_MAX_AMOUNT' => [
                    'type' => 'text',
                    'label' => 'Maximum amount (€)',
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'size' => 20,
                        'desc' => 'Maximum purchase amount to activate this plan',
                    ],
                ],
                'ALMA_' . $planKey . '_SORT_ORDER' => [
                    'type' => 'text',
                    'label' => 'Position',
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'size' => 20,
                        'desc' => 'Use relative values to set the order on the checkout page',
                    ],
                ]
            ]);
        }

        return $feePlansFields;
    }

    public function fieldsValue(): array
    {
        $feePlansFieldsValue = [];
        $feePlansProvider = $this->feePlanProvider->getFeePlanList();

        foreach ($feePlansProvider as $key => $feePlan) {
            $orderPlan = $key + 1;
            /** @var FeePlan $feePlan */
            $planKey = mb_strtoupper($feePlan->getPlanKey());
            $feePlansFieldsValue = array_merge($feePlansFieldsValue, [
                'ALMA_' . $planKey . '_STATE' => $feePlan->getPlanKey() === 'general_3_0_0',
                'ALMA_' . $planKey . '_MIN_AMOUNT' => PriceHelper::priceToEuro($feePlan->getMinPurchaseAmount()),
                'ALMA_' . $planKey . '_MAX_AMOUNT' => PriceHelper::priceToEuro($feePlan->getMaxPurchaseAmount()),
                'ALMA_' . $planKey . '_SORT_ORDER' => $orderPlan,
            ]);
        }

        return $feePlansFieldsValue;
    }
}
