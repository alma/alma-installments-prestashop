<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class RefundAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_REFUND_ON_CHANGE_STATE = 'ALMA_REFUND_ON_CHANGE_STATE';
    public const KEY_FIELD_STATE_REFUND_SELECT = 'ALMA_STATE_REFUND_SELECT';

    public static function title(): string
    {
        return 'Refund with state change';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
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
                'label' => 'Activate refund by change state',
                'required' => false,
                'form' => 'refund_on_change',
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
            self::KEY_FIELD_STATE_REFUND_SELECT => $dynamicForm
        ];
    }
}
