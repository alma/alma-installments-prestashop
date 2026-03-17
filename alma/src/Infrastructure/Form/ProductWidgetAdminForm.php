<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ProductWidgetAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_PRODUCT_WIDGET_STATE = 'ALMA_PRODUCT_WIDGET_STATE';
    public const KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE = 'ALMA_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE';

    public static function title(): string
    {
        return 'Display widget on product page';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            'ALMA_EMBED_WIDGET_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'product_widget',
                'options' => [
                    'col' => 12,
                    'html_content' => $templateHtml,
                ],
            ],
            self::KEY_FIELD_PRODUCT_WIDGET_STATE => [
                'type' => 'switch',
                'label' => 'Display widget',
                'required' => false,
                'form' => 'product_widget',
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
            self::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE => [
                'type' => 'switch',
                'label' => 'Display even if the product is not eligible',
                'required' => false,
                'form' => 'product_widget',
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
