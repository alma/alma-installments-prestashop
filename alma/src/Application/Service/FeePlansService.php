<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FeePlansService
{
    private \Context $context;
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlanProvider;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function __construct(
        \Context $context,
        FeePlansProvider $feePlanProvider,
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy
    ) {
        $this->context = $context;
        $this->feePlanProvider = $feePlanProvider;
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Create the fee plans tabs template with the fee plans list from fee plan provider to create nav tabs in the fee plans template
     * @return string
     */
    public function createTemplateTabs(): string
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

    /**
     * Get fee plans fields to build the array for loop the fields in the form
     * @return array
     */
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

    /**
     * Get fee plans fields value for set the value in the form.
     * If merchant id is not saved in DB, get value from fee plan provider,
     * else get value from post (Tools::getValue) for each field of plan
     * @return array
     */
    public function fieldsValue(): array
    {
        $feePlansFieldsValue = [];
        $feePlansProvider = $this->feePlanProvider->getFeePlanList();

        /** @var FeePlan $feePlan */
        foreach ($feePlansProvider as $key => $feePlan) {
            $state = $feePlan->getPlanKey() === 'general_3_0_0';
            $minAmount = PriceHelper::priceToEuro($feePlan->getMinPurchaseAmount());
            $maxAmount = PriceHelper::priceToEuro($feePlan->getMaxPurchaseAmount());
            $orderPlan = $key + 1;
            $planKey = mb_strtoupper($feePlan->getPlanKey());

            $keyFieldFeePlanState = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, $planKey);
            $keyFieldFeePlanMinAmount = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_MIN_AMOUNT, $planKey);
            $keyFieldFeePlanMaxAmount = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_MAX_AMOUNT, $planKey);
            $keyFieldFeePlanSortOrder = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_SORT_ORDER, $planKey);

            if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
                $state = $this->toolsProxy->getValue($keyFieldFeePlanState);
                $minAmount = $this->toolsProxy->getValue($keyFieldFeePlanMinAmount);
                $maxAmount = $this->toolsProxy->getValue($keyFieldFeePlanMaxAmount);
                $orderPlan = $this->toolsProxy->getValue($keyFieldFeePlanSortOrder);
            }

            $feePlansFieldsValue = array_merge($feePlansFieldsValue, [
                $keyFieldFeePlanState => $state,
                $keyFieldFeePlanMinAmount => $minAmount,
                $keyFieldFeePlanMaxAmount => $maxAmount,
                $keyFieldFeePlanSortOrder => $orderPlan,
            ]);
        }

        return $feePlansFieldsValue;
    }
}
