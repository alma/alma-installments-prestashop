<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class PaymentButtonAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_PAYNOW_BUTTON_TITLE = 'ALMA_PAYNOW_BUTTON_TITLE';
    public const KEY_FIELD_PAYNOW_BUTTON_DESC = 'ALMA_PAYNOW_BUTTON_DESC';
    public const KEY_FIELD_PNX_BUTTON_TITLE = 'ALMA_PNX_BUTTON_TITLE';
    public const KEY_FIELD_PNX_BUTTON_DESC = 'ALMA_PNX_BUTTON_DESC';
    public const KEY_FIELD_PAYLATER_BUTTON_TITLE = 'ALMA_PAYLATER_BUTTON_TITLE';
    public const KEY_FIELD_PAYLATER_BUTTON_DESC = 'ALMA_PAYLATER_BUTTON_DESC';
    public const KEY_FIELD_CREDIT_BUTTON_TITLE = 'ALMA_CREDIT_BUTTON_TITLE';
    public const KEY_FIELD_CREDIT_BUTTON_DESC = 'ALMA_CREDIT_BUTTON_DESC';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Payment method configuration', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();

        return [
            'ALMA_PAYMENT_BUTTON_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'payment_button',
                'options' => [
                    'col' => 12,
                    'html_content' => $templateHtml,
                ],
            ],
            'ALMA_PAYNOW_BUTTON_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'payment_button',
                'options' => [
                    'col' => 12,
                    'html_content' => '<h2>Pay now</h2>',
                ],
            ],
            self::KEY_FIELD_PAYNOW_BUTTON_TITLE => [
                'type' => 'text',
                'label' => $translator->trans('Title', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            self::KEY_FIELD_PAYNOW_BUTTON_DESC => [
                'type' => 'text',
                'label' => $translator->trans('Description', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            'ALMA_PNX_BUTTON_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'payment_button',
                'options' => [
                    'col' => 12,
                    'html_content' => '<h2>' . $translator->trans('Payments in 2, 3 and 4 installments', [], 'Modules.Alma.Settings') . '</h2>',
                ],
            ],
            self::KEY_FIELD_PNX_BUTTON_TITLE => [
                'type' => 'text',
                'label' => $translator->trans('Title', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            self::KEY_FIELD_PNX_BUTTON_DESC => [
                'type' => 'text',
                'label' => $translator->trans('Description', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            'ALMA_PAYLATER_BUTTON_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'payment_button',
                'options' => [
                    'col' => 12,
                    'html_content' => '<h2>' . $translator->trans('Deferred payments', [], 'Modules.Alma.Settings') . '</h2>',
                ],
            ],
            self::KEY_FIELD_PAYLATER_BUTTON_TITLE => [
                'type' => 'text',
                'label' => $translator->trans('Title', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            self::KEY_FIELD_PAYLATER_BUTTON_DESC => [
                'type' => 'text',
                'label' => $translator->trans('Description', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            'ALMA_CREDIT_BUTTON_HTML' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'payment_button',
                'options' => [
                    'col' => 12,
                    'html_content' => '<h2>' . $translator->trans('Payments in more than 4 installments', [], 'Modules.Alma.Settings') . '</h2>',
                ],
            ],
            self::KEY_FIELD_CREDIT_BUTTON_TITLE => [
                'type' => 'text',
                'label' => $translator->trans('Title', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ],
            self::KEY_FIELD_CREDIT_BUTTON_DESC => [
                'type' => 'text',
                'label' => $translator->trans('Description', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true,
                ],
            ]
        ];
    }
}
