<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class InPageAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_INPAGE_STATE = 'ALMA_INPAGE_STATE';
    public const KEY_FIELD_INPAGE_PAYMENT_BUTTON_SELECTOR = 'ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR';
    public const KEY_FIELD_INPAGE_PLACE_ORDER_BUTTON_SELECTOR = 'ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR';

    public static function title(): string
    {
        return 'In-page checkout';
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        return [
            self::KEY_FIELD_INPAGE_STATE => [
                'type' => 'switch',
                'label' => 'Activate in-page checkout',
                'required' => false,
                'form' => 'inpage',
                'encrypted' => false,
                'options' => [
                    'desc' => 'Let your customers pay with Alma in a secure pop-up, without leaving your site. Learn more.',
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
            self::KEY_FIELD_INPAGE_PAYMENT_BUTTON_SELECTOR => [
                'type' => 'text',
                'label' => 'Input payment button Alma selector',
                'required' => false,
                'form' => 'inpage',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => 'Advanced [Optional] CSS selector used by our scripts to identify the Alma payment button',
                ],
            ],
            self::KEY_FIELD_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => [
                'type' => 'text',
                'label' => 'Place order button selector',
                'required' => false,
                'form' => 'inpage',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'desc' => 'Advanced [Optional] CSS selector used by our scripts to identify the payment confirmation button',
                ],
            ],
        ];
    }
}
