<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class DebugAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_DEBUG_STATE = 'ALMA_DEBUG_STATE';

    public static function title(): string
    {
        return 'Debug options';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            self::KEY_FIELD_DEBUG_STATE => [
                'type' => 'switch',
                'label' => 'Activate logging',
                'required' => false,
                'form' => 'debug_mode',
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
        ];
    }
}
