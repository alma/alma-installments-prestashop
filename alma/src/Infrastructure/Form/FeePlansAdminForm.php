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
            // TODO : move this html_content in a template file (try twig)
            'ALMA_TABS' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'fee_plans',
                'options' => [
                    'html_content' => "
                        <ul class='nav nav-tabs alma-pnx-tabs'>
                            <li class='active'>
                                <a href='#general_3_0_0' data-toggle='tab'>P3X</a>
                            </li>
                            <li>
                                <a href='#general_4_0_0' data-toggle='tab'>P4X</a>
                            </li>
                        </ul>
                        <script type='text/javascript'>
                            $(document).ready(function() {
                                function showTab(tab) {
                                    $('.tab-general_3_0_0, .tab-general_4_0_0').hide();
                                    $('.tab-' + tab).show();
                                }
                                showTab('general_3_0_0');
                                $('.alma-pnx-tabs a').click(function(e) {
                                    e.preventDefault();
                                    $('.alma-pnx-tabs li').removeClass('active');
                                    $(this).parent().addClass('active');
                                    const tab = $(this).attr('href').replace('#', '');
                                    showTab(tab);
                                });
                            });
                        </script>
                    ",
                ],
            ],
            'ALMA_GENERAL_3_0_0_STATE' => [
                'type' => 'switch',
                'label' => 'Enable 3 installments payment',
                'required' => false,
                'form' => 'fee_plans',
                'encrypted' => false,
                'options' => [
                    'form_group_class' => 'tab-general_3_0_0',
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
                    'form_group_class' => 'tab-general_3_0_0',
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
                    'form_group_class' => 'tab-general_3_0_0',
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
                    'form_group_class' => 'tab-general_3_0_0',
                    'size' => 20,
                    'desc' => 'Use relative values to set the order on the checkout page',
                ],
            ],
            'ALMA_GENERAL_4_0_0_STATE' => [
                'type' => 'switch',
                'label' => 'Enable 4 installments payment',
                'required' => false,
                'form' => 'fee_plans',
                'encrypted' => false,
                'options' => [
                    'form_group_class' => 'tab-general_4_0_0',
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
        ];
    }
}
