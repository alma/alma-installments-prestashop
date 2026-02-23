<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class FeePlansAdminForm extends AbstractAdminForm
{
    public static function title(): string
    {
        return 'Installments plans';
    }

    public static function fieldsForm(): array
    {
        return [
            'ALMA_GENERAL_3_0_0_STATE' => [
                'type' => 'switch',
                'label' => 'Enable 3 installments payment',
                'required' => false,
                'form' => 'fee_plans',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => 'Enabled'
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => 'Disabled'
                        ]
                    ],
                ],
            ],
            'ALMA_GENERAL_3_0_0_MIN_AMOUNT' => [
                'type' => 'text',
                'label' => 'Minimum amount (€)',
                'required' => false,
                'form' => 'fee_plans',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => 'Minimum purchase amount to activate this plan',
                ],
            ],
            'ALMA_GENERAL_3_0_0_MAX_AMOUNT' => [
                'type' => 'text',
                'label' => 'Maximum amount (€)',
                'required' => false,
                'form' => 'fee_plans',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => 'Maximum purchase amount to activate this plan',
                ],
            ],
            'ALMA_GENERAL_3_0_0_SORT_ORDER' => [
                'type' => 'text',
                'label' => 'Position',
                'required' => false,
                'form' => 'fee_plans',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => 'Use relative values to set the order on the checkout page',
                ],
            ],
        ];
    }
}
