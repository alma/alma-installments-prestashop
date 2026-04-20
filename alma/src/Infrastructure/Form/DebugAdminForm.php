<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class DebugAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_DEBUG_STATE = 'ALMA_DEBUG_STATE';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Debug options', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();
        return [
            self::KEY_FIELD_DEBUG_STATE => [
                'type' => 'switch',
                'label' => $translator->trans('Activate logging', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'debug_mode',
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
        ];
    }
}
