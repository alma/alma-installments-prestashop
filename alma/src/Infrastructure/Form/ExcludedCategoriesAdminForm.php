<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ExcludedCategoriesAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE = 'ALMA_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE';
    public const KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE = 'ALMA_EXCLUDED_CATEGORIES_MESSAGE';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Excluded categories', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();
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
                'label' => $translator->trans('Display message', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'excluded_categories',
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
            self::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE => [
                'type' => 'text',
                'label' => $translator->trans('Excluded categories non-eligibility message', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'excluded_categories',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => $translator->trans(
                        'Message displayed on an excluded product page or on the cart page if it contains an excluded product.',
                        [],
                        'Modules.Alma.Settings'
                    ),
                    'lang' => true,
                ],
            ]
        ];
    }
}
