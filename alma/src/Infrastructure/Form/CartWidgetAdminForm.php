<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class CartWidgetAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_CART_WIDGET_STATE = 'ALMA_CART_WIDGET_STATE';
    public const KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE = 'ALMA_CART_WIDGET_DISPLAY_NOT_ELIGIBLE';
    public const KEY_FIELD_CART_WIDGET_POSITION_CUSTOM = 'ALMA_CART_WIDGET_POSITION_CUSTOM'; // Old key for custom position on our module v5
    public const KEY_FIELD_CART_WIDGET_POSITION_SELECTOR = 'ALMA_CART_WDGT_POS_SELECTOR'; // Old key for custom position selector on our module v5

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Display widget on cart page', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();
        return [
            self::KEY_FIELD_CART_WIDGET_STATE => [
                'type' => 'switch',
                'label' => $translator->trans('Display widget', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'cart_widget',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => $translator->trans('Enabled', [], 'Modules.Alma.Settings'),
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => $translator->trans('Disabled', [], 'Modules.Alma.Settings')
                        ]
                    ],
                ],
            ],
            self::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE => [
                'type' => 'switch',
                'label' => $translator->trans('Display even if the cart is not eligible', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'cart_widget',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => $translator->trans('Enabled', [], 'Modules.Alma.Settings'),
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => $translator->trans('Disabled', [], 'Modules.Alma.Settings')
                        ]
                    ],
                ],
            ]
        ];
    }
}
