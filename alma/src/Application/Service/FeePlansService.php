<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\Exception\ParametersException;
use Alma\Client\Domain\Entity\FeePlan;
use Alma\Client\Domain\Entity\FeePlanList;
use PrestaShop\Module\Alma\Application\Assembler\FeePlanListAssembler;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ValidatorForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FeePlansService
{
    private \Context $context;
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;
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
        $this->feePlansProvider = $feePlanProvider;
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
        try {
            $tpl->assign([
                'fee_plans' => $this->feePlansTabs(),
            ]);
        } catch (ParametersException $e) {
            // TODO: Add log here
            $tpl->assign([
                'fee_plans' => [],
            ]);
        }

        return $tpl->fetch();
    }

    /**
     * Get fee plans for loop the tabs in the fee plans template
     * @return array
     */
    public function feePlansTabs(): array
    {
        $feePlansTabs = [];
        $feePlanListAssembled = [];
        $feePlanList = $this->feePlansProvider->getFeePlanList();

        foreach ($feePlanList as $feePlan) {
            $planKey = mb_strtoupper($feePlan->getPlanKey());

            $feePlanListAssembled[] = [
                'enabled' => (bool) $this->configurationRepository->get(sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, $planKey)),
                'plan_key' => $feePlan->getPlanKey(),
                'title' => FeePlanPresenter::getTitle($feePlan),
            ];
        }

        $enabledPlans = array_filter($feePlanListAssembled, fn ($plan) => $plan['enabled']);
        $firstEnabledPlan = !empty($enabledPlans) ? reset($enabledPlans) : null;

        foreach ($feePlanListAssembled as $feePlan) {
            $feePlansTabs[$feePlan['plan_key']] = [
                'title' => $feePlan['title'],
                'active' => $feePlan['enabled'],
                'firstPlanEnable' => $firstEnabledPlan['plan_key'] ?? 'general_3_0_0',
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
        $feePlansProvider = $this->feePlansProvider->getFeePlanList();

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
                        'readonly' => $feePlan->isPayNow(),
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
     * From the loop of fee plan list.
     * @return array
     */
    public function fieldsValue(): array
    {
        $feePlansFieldsValue = [];
        $feePlansProvider = $this->feePlansProvider->getFeePlanList();

        /** @var FeePlan $feePlan */
        foreach ($feePlansProvider as $feePlan) {
            $planKey = mb_strtoupper($feePlan->getPlanKey());

            $keyFieldFeePlanState = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, $planKey);
            $keyFieldFeePlanMinAmount = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_MIN_AMOUNT, $planKey);
            $keyFieldFeePlanMaxAmount = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_MAX_AMOUNT, $planKey);
            $keyFieldFeePlanSortOrder = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_SORT_ORDER, $planKey);

            $state = $this->configurationRepository->get($keyFieldFeePlanState);
            $minAmount = $this->configurationRepository->get($keyFieldFeePlanMinAmount);
            $maxAmount = $this->configurationRepository->get($keyFieldFeePlanMaxAmount);
            $sortOrder = $this->configurationRepository->get($keyFieldFeePlanSortOrder);

            $feePlansFieldsValue = array_merge($feePlansFieldsValue, [
                $keyFieldFeePlanState => $state,
                $keyFieldFeePlanMinAmount => $minAmount,
                $keyFieldFeePlanMaxAmount => $maxAmount,
                $keyFieldFeePlanSortOrder => $sortOrder,
            ]);
        }

        return $feePlansFieldsValue;
    }

    public function fieldsToSaveFromApi(FeePlanList $feePlanList): array
    {
        $feePlans = [];

        foreach ($feePlanList as $key => $feePlan) {
            $installmentsCount = $feePlan->getInstallmentsCount();
            $deferredDays = $feePlan->getDeferredDays();
            $deferredMonths = $feePlan->getDeferredMonths();

            $planKey = sprintf('general_%d_%d_%d', $installmentsCount, $deferredDays, $deferredMonths);
            $sortOrder = $key + 1;

            $feePlans[$planKey] = [
                'state' => $feePlan->isAllowed() ? '1' : '0',
                'min_amount' => (string) $feePlan->getMinPurchaseAmount(),
                'max_amount' => (string) $feePlan->getMaxPurchaseAmount(),
                'sort_order' => (string) $sortOrder,
            ];
        }

        return [
            'ALMA_FEE_PLAN_LIST' => json_encode($feePlans)
        ];
    }

    /**
     * Build the fee plan list in JSON format to save in the database with the key ALMA_FEE_PLAN_LIST
     * The fee plan list is build with the fields from post with the prefix ALMA_GENERAL_ and the pattern ALMA_GENERAL_{installments_count}_{deferred_months}_{deferred_days}_{field_name}
     * For example, for a fee plan with 3 installments, 0 deferred months and 0 deferred days, the field for state will be ALMA_GENERAL_3_0_0_STATE, for min amount will be ALMA_GENERAL_3_0_0_MIN_AMOUNT, for max amount will be ALMA_GENERAL_3_0_0_MAX_AMOUNT and for sort order will be ALMA_GENERAL_3_0_0_SORT_ORDER
     * @param array $formData
     * @return array
     */
    public function fieldsToSaveFromPost(array $formData): array
    {
        $feePlans = [];
        $result = [];

        foreach ($formData as $key => $value) {
            if (preg_match('/^ALMA_GENERAL_(\d+)_(\d+)_(\d+)_(.+)$/', $key, $matches)) {
                $planKey = sprintf('general_%s_%s_%s', $matches[1], $matches[2], $matches[3]);
                $fieldName = strtolower($matches[4]);
                if ($fieldName === 'min_amount' || $fieldName === 'max_amount') {
                    $value = (string) PriceHelper::priceToCent($value);
                }
                $feePlans[$planKey][$fieldName] = $value;
            }
        }

        $result[FeePlansAdminForm::KET_FIELD_FEE_PLAN_LIST] = json_encode($feePlans);

        return $result;
    }
}
