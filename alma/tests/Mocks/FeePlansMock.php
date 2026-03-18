<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Application\Presenter\FeePlanPresenter;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;

final class FeePlansMock
{
    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public static function feePlan($installmentCount, $deferredDays = 0, $deferredMonths = 0, $allowed = true, $minAmount = 10000, $maxAmount = 200000): FeePlan
    {
        return new FeePlan([
            'allowed' => $allowed,
            'available_online' => true,
            'customer_fee_variable' => 0,
            'deferred_days' => $deferredDays,
            'deferred_months' => $deferredMonths,
            'installments_count' => $installmentCount,
            'kind' => 'general',
            'max_purchase_amount' => $maxAmount,
            'merchant_fee_variable' => 0,
            'merchant_fee_fixed' => 0,
            'min_purchase_amount' => $minAmount,
        ]);
    }

    public static function feePlanAssembled($installmentCount, $deferredDays = 0, $deferredMonths = 0, $allowed = true, $minAmount = 10000, $maxAmount = 200000, $enabled = false, $sortOrder = 0): array
    {
        return [
            'allowed' => $allowed,
            'available_online' => true,
            'customer_fee_variable' => 0,
            'deferred_days' => $deferredDays,
            'deferred_months' => $deferredMonths,
            'installments_count' => $installmentCount,
            'kind' => 'general',
            'max_purchase_amount' => $maxAmount,
            'merchant_fee_variable' => 0,
            'merchant_fee_fixed' => 0,
            'min_purchase_amount' => $minAmount,
            'enabled' => $enabled,
            'sort_order' => $sortOrder,
        ];
    }

    public static function feePlansTabsExpected($installmentCount, $active = false, $deferredDays = 0, $deferredMonth = 0, $firstPlanEnable = 'general_3_0_0'): array
    {
        return [
            sprintf('general_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth) => [
                'title' => FeePlanPresenter::getTitle(FeePlansMock::feePlan($installmentCount)),
                'active' => $active,
                'firstPlanEnable' => $firstPlanEnable,
            ],
        ];
    }

    public static function feePlanHtmlTemplate($templateHtml = ''): array
    {
        return [
            'ALMA_TABS' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'fee_plans',
                'options' => [
                    'col' => 12,
                    'html_content' => $templateHtml,
                ],
            ]
        ];
    }

    public static function feePlanFieldsExpected($installmentCount, $deferredDays = 0, $deferredMonth = 0): array
    {
        $planKey = sprintf('GENERAL_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);
        $planKeyTab = sprintf('general_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);
        $readonly = $planKey === 'GENERAL_1_0_0';

        return [
            'ALMA_' . $planKey . '_STATE' => [
                'type' => 'switch',
                'label' => FeePlanPresenter::getLabel(FeePlansMock::feePlan($installmentCount)),
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
                    'readonly' => $readonly,
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
        ];
    }

    public static function feePlanFieldsValueExpected(int $installmentCount, $deferredDays = 0, $deferredMonth = 0, $enable = 1, $minAmount = 10000, $maxAmount = 200000, $sortOrder = 1): array
    {
        $planKey = sprintf('GENERAL_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);

        return [
            'ALMA_' . $planKey . '_STATE' => $enable,
            'ALMA_' . $planKey . '_MIN_AMOUNT' => PriceHelper::priceToEuro($minAmount),
            'ALMA_' . $planKey . '_MAX_AMOUNT' => PriceHelper::priceToEuro($maxAmount),
            'ALMA_' . $planKey . '_SORT_ORDER' => $sortOrder,
        ];
    }

    public static function almaFeePlanForDbExpected(int $installmentCount, int $deferredDays = 0, int $deferredMonth = 0, string $state = '1', string $minAmount = '10000', string $maxAmount = '200000', $sortOrder = '1'): array
    {
        $planKey = sprintf('general_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);

        return [
            FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST => json_encode([
                $planKey => [
                    'state' => $state,
                    'min_amount' => $minAmount,
                    'max_amount' => $maxAmount,
                    'sort_order' => $sortOrder,
                ]
            ])
        ];
    }

    public static function almaFeePlanFromDb(int $installmentCount, int $deferredDays = 0, int $deferredMonth = 0, string $state = '1', string $minAmount = '10000', string $maxAmount = '200000', $sortOrder = '1'): array
    {
        $planKey = sprintf('general_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);
        return [
            $planKey => [
                'state' => $state,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'sort_order' => $sortOrder,
            ]
        ];
    }
}
