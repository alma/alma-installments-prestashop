<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class CartWidgetAdminForm extends AbstractAdminForm
{
    public static function title(): string
    {
        return 'Display widget on cart page';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            'ALMA_SHOW_CART_ELIGIBILITY' => [
                'type' => 'switch',
                'label' => 'Display widget',
                'required' => false,
                'form' => 'cart_widget',
                'encrypted' => false,
                'options' => [
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
            'ALMA_CART_WDGT_NOT_ELGBL' => [
                'type' => 'switch',
                'label' => 'Display even if the cart is not eligible',
                'required' => false,
                'form' => 'cart_widget',
                'encrypted' => false,
                'options' => [
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
            ]
        ];
    }
}
