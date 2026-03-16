<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\Exception\ParametersException;
use Alma\Client\Domain\Entity\FeePlan;
use Alma\Client\Domain\Entity\FeePlanList;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

class FeePlansService
{
    private \Context $context;
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;
    /**
     * @var FeePlanPresenter
     */
    private FeePlanPresenter $feePlanPresenter;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        \Context $context,
        FeePlansProvider $feePlanProvider,
        FeePlanPresenter $feePlanPresenter,
        TranslatorInterface $translator,
        ConfigurationRepository $configurationRepository,
    ) {
        $this->context = $context;
        $this->feePlanProvider = $feePlanProvider;
        $this->feePlanPresenter = $feePlanPresenter;
        $this->configurationRepository = $configurationRepository;
        $this->translator = $translator;
    }

    /**
     * Create the fee plans tabs template with the fee plans list from fee plan provider to create nav tabs in the fee plans template
     * @return string
     */
    public function createTemplateTabs(): string
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/fee_plans_tabs.tpl');
        try {
            $feePlans = $this->feePlansTabs();
        } catch (ParametersException $e) {
            // TODO: Add log here
             $feePlans = [];
        }
        $tpl->assign([
            'fee_plans' => $feePlans,
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
                    'label' => $this->feePlanPresenter->getLabel($feePlan),
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'values' => [
                            [
                                'id' => 'ENABLE',
                                'value' => 1,
                                'label' => $this->translator->trans('Enabled', [], 'Modules.Alma.Settings'),
                            ],
                            [
                                'id' => 'DISABLE',
                                'value' => 0,
                                'label' => $this->translator->trans('Disabled', [], 'Modules.Alma.Settings')
                            ]
                        ],
                    ],
                ],
                'ALMA_' . $planKey . '_MIN_AMOUNT' => [
                    'type' => 'text',
                    'label' => $this->translator->trans('Minimum amount (€)', [], 'Modules.Alma.Settings'),
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'readonly' => $feePlan->isPayNow(),
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'size' => 20,
                        'desc' => $this->translator->trans('Minimum purchase amount to activate this plan', [], 'Modules.Alma.Settings'),
                    ],
                ],
                'ALMA_' . $planKey . '_MAX_AMOUNT' => [
                    'type' => 'text',
                    'label' => $this->translator->trans('Maximum amount (€)', [], 'Modules.Alma.Settings'),
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'size' => 20,
                        'desc' => $this->translator->trans('Maximum purchase amount to activate this plan', [], 'Modules.Alma.Settings'),
                    ],
                ],
                'ALMA_' . $planKey . '_SORT_ORDER' => [
                    'type' => 'text',
                    'label' => $this->translator->trans('Position', [], 'Modules.Alma.Settings'),
                    'required' => false,
                    'form' => 'fee_plans',
                    'encrypted' => false,
                    'options' => [
                        'form_group_class' => 'tab-' . $planKeyTab,
                        'size' => 20,
                        'desc' => $this->translator->trans('Use relative values to set the order on the checkout page', [], 'Modules.Alma.Settings'),
                    ],
                ]
            ]);
        }

        return $feePlansFields;
    }

    /**
     * Get fee plans fields value for set the value in the form.
     * From the loop of fee plan list.
     * @param $feePlanConfiguration
     * @return array
     */
    public function fieldsValue($feePlanConfiguration): array
    {
        $feePlansFieldsValue = [];
        $feePlansProvider = $this->feePlansProvider->getFeePlanList();

        /** @var FeePlan $feePlan */
        foreach ($feePlanConfiguration as $planKey => $feePlan) {
            $planKey = mb_strtoupper($planKey);
            $keyFieldFeePlanState = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_STATE, $planKey);
            $keyFieldFeePlanMinAmount = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_MIN_AMOUNT, $planKey);
            $keyFieldFeePlanMaxAmount = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_MAX_AMOUNT, $planKey);
            $keyFieldFeePlanSortOrder = sprintf(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_SORT_ORDER, $planKey);

            $feePlansFieldsValue = array_merge($feePlansFieldsValue, [
                $keyFieldFeePlanState => $feePlan['state'],
                $keyFieldFeePlanMinAmount => PriceHelper::priceToEuro($feePlan['min_amount']),
                $keyFieldFeePlanMaxAmount => PriceHelper::priceToEuro($feePlan['max_amount']),
                $keyFieldFeePlanSortOrder => $feePlan['sort_order'],
            ]);
        }

        return $feePlansFieldsValue;
    }

    /**
     * Build the fee plan list in JSON format to save in the database with the key ALMA_FEE_PLAN_LIST
     * The fee plan list is build with the fields from API with the pattern general_{installments_count}_{deferred_months}_{deferred_days}
     * For example, for a fee plan with 3 installments, 0 deferred months and 0 deferred days, the key will be general_3_0_0
     * @param FeePlanList $feePlanList
     * @return array
     */
    public function fieldsToSaveFromApi(FeePlanList $feePlanList): array
    {
        $feePlans = [];

        foreach ($feePlanList as $key => $feePlan) {
            /** @var FeePlan $feePlan */
            $installmentsCount = $feePlan->getInstallmentsCount();
            $deferredDays = $feePlan->getDeferredDays();
            $deferredMonths = $feePlan->getDeferredMonths();

            $planKey = sprintf('general_%d_%d_%d', $installmentsCount, $deferredDays, $deferredMonths);
            $sortOrder = $key + 1;

            $feePlans[$planKey] = [
                'state' => $feePlan->getPlanKey() === 'general_3_0_0' ? '1' : '0',
                'min_amount' => (string) $feePlan->getMinPurchaseAmount(),
                'max_amount' => (string) $feePlan->getMaxPurchaseAmount(),
                'sort_order' => (string) $sortOrder,
            ];
        }

        return [
            FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST => json_encode($feePlans)
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

        $result[FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST] = json_encode($feePlans);

        return $result;
    }
}
