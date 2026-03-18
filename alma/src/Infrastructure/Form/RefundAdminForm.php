<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class RefundAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_REFUND_ON_CHANGE_STATE = 'ALMA_REFUND_ON_CHANGE_STATE';
    public const KEY_FIELD_STATE_REFUND_SELECT = 'ALMA_STATE_REFUND_SELECT';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Refund with state change', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();

        return [
            'ALMA_REFUND_ON_CHANGE_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'refund_on_change',
                'options' => [
                    'col' => 12,
                    'html_content' => $templateHtml,
                ],
            ],
            self::KEY_FIELD_REFUND_ON_CHANGE_STATE => [
                'type' => 'switch',
                'label' => $translator->trans('Activate refund by change state', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'refund_on_change',
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
            self::KEY_FIELD_STATE_REFUND_SELECT => $dynamicForm
        ];
    }
}
