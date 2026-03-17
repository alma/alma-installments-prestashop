<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class CartWidgetAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_CART_WIDGET_STATE = 'ALMA_CART_WIDGET_STATE';
    public const KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE = 'ALMA_CART_WIDGET_DISPLAY_NOT_ELIGIBLE';

    public static function title(): string
    {
        return 'Display widget on cart page';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            self::KEY_FIELD_CART_WIDGET_STATE => [
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
            self::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE => [
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
