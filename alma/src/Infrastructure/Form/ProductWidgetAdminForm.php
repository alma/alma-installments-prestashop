<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ProductWidgetAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_PRODUCT_WIDGET_STATE = 'ALMA_PRODUCT_WIDGET_STATE';
    public const KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE = 'ALMA_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Display widget on product page', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();

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
                'label' => $translator->trans('Display widget', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'product_widget',
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
            self::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE => [
                'type' => 'switch',
                'label' => $translator->trans('Display even if the product is not eligible', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'product_widget',
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
