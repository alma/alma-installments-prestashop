<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ProductWidgetAdminForm extends AbstractAdminForm
{
    public static function title(): string
    {
        return 'Display widget on product page';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            'ALMA_SHOW_PRODUCT_ELIGIBILITY' => [
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
            'ALMA_PRODUCT_WDGT_NOT_ELGBL' => [
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
