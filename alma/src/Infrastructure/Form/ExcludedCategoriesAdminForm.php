<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ExcludedCategoriesAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE = 'ALMA_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE';
    public const KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE = 'ALMA_EXCLUDED_CATEGORIES_MESSAGE';

    public static function title(): string
    {
        return 'Excluded categories';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            'ALMA_EXCLUDED_CATEGORIES_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'excluded_categories',
                'options' => [
                    'col' => 12,
                    'html_content' => $templateHtml,
                ],
            ],
            self::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => [
                'type' => 'switch',
                'label' => 'Display message',
                'required' => false,
                'form' => 'excluded_categories',
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
            self::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE => [
                'type' => 'text',
                'label' => 'Excluded categories non-eligibility message',
                'required' => false,
                'form' => 'excluded_categories',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => 'Message displayed on an excluded product page or on the cart page if it contains an excluded product.'
                ],
            ]
        ];
    }
}
