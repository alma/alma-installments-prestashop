<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class InPageAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_INPAGE_STATE = 'ALMA_INPAGE_STATE';
    public const KEY_FIELD_INPAGE_PAYMENT_BUTTON_SELECTOR = 'ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR';
    public const KEY_FIELD_INPAGE_PLACE_ORDER_BUTTON_SELECTOR = 'ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('In-page checkout', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();

        return [
            self::KEY_FIELD_INPAGE_STATE => [
                'type' => 'switch',
                'label' => $translator->trans('Activate in-page checkout', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'inpage',
                'encrypted' => false,
                'options' => [
                    'desc' => $translator->trans(
                        'Let your customers pay with Alma in a secure pop-up, without leaving your site. Learn more.',
                        [],
                        'Modules.Alma.Settings'
                    ),
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
            self::KEY_FIELD_INPAGE_PAYMENT_BUTTON_SELECTOR => [
                'type' => 'text',
                'label' => $translator->trans('Input payment button Alma selector', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'inpage',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => $translator->trans(
                        'Advanced [Optional] CSS selector used by our scripts to identify the Alma payment button',
                        [],
                        'Modules.Alma.Settings'
                    ),
                ],
            ],
            self::KEY_FIELD_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => [
                'type' => 'text',
                'label' => $translator->trans('Place order button selector', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'inpage',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => $translator->trans(
                        'Advanced [Optional] CSS selector used by our scripts to identify the payment confirmation button',
                        [],
                        'Modules.Alma.Settings'
                    ),
                ],
            ],
        ];
    }
}
