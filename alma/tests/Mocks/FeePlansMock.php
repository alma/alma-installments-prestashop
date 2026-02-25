<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;

final class FeePlansMock
{
    public static function feePlan($installmentCount, $allowed = true, $minAmount = 10000, $maxAmount = 200000): FeePlan
    {
        return new FeePlan([
            'allowed' => $allowed,
            'available_online' => true,
            'customer_fee_variable' => 0,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'installments_count' => $installmentCount,
            'kind' => 'general',
            'max_purchase_amount' => $maxAmount,
            'merchant_fee_variable' => 0,
            'merchant_fee_fixed' => 0,
            'min_purchase_amount' => $minAmount,
        ]);
    }

    public static function feePlansTabsExpected($installmentCount, $active = false, $deferredDays = 0, $deferredMonth = 0): array
    {
        return [
            sprintf('general_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth) => [
                'title' => FeePlanHelper::getTitle($installmentCount, $deferredDays, $deferredMonth),
                'active' => $active,
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
                    'html_content' => $templateHtml,
                ],
            ]
        ];
    }

    public static function feePlanFieldsExpected($installmentCount, $deferredDays = 0, $deferredMonth = 0): array
    {
        $planKey = sprintf('GENERAL_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);
        $planKeyTab = sprintf('general_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);

        return [
            'ALMA_' . $planKey . '_STATE' => [
                'type' => 'switch',
                'label' => FeePlanHelper::getLabel($installmentCount, $deferredDays, $deferredMonth),
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
        ];
    }

    public static function feePlanFieldsValueExpected(int $installmentCount, $deferredDays = 0, $deferredMonth = 0): array
    {
        $planKey = sprintf('GENERAL_%d_%d_%d', $installmentCount, $deferredDays, $deferredMonth);

        return [
            'ALMA_' . $planKey . '_STATE' => 1,
            'ALMA_' . $planKey . '_MIN_AMOUNT' => PriceHelper::priceToEuro(10000),
            'ALMA_' . $planKey . '_MAX_AMOUNT' => PriceHelper::priceToEuro(200000),
            'ALMA_' . $planKey . '_SORT_ORDER' => 1,
        ];
    }
}
