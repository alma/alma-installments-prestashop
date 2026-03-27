<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

final class FieldsMock
{
    public static function allFields(): array
    {
        return [
            'classic_field_lang_true' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true
                ]
            ],
            'classic_field_lang_false' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => false
                ]
            ],
            'classic_field' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                ]
            ],
        ];
    }

    public static function fieldsWithLangTrue(): array
    {
        return [
            'classic_field_lang_true' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true
                ]
            ],
        ];
    }

    public static function fieldsWithLangTrueExpected(string $keyName): array
    {
        return [
            $keyName => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => true
                ]
            ],
        ];
    }

    public static function fieldsWithLangFalse(): array
    {
        return [
            'classic_field_lang_false' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'form' => 'payment_button',
                'encrypted' => false,
                'options' => [
                    'size' => 20,
                    'lang' => false
                ]
            ],
        ];
    }

     public static function fieldsWithoutLang(): array
     {
         return [
             'classic_field' => [
                 'type' => 'text',
                 'label' => 'Title',
                 'required' => true,
                 'form' => 'payment_button',
                 'encrypted' => false,
                 'options' => [
                     'size' => 20,
                 ]
             ],
         ];
     }

     public static function fieldsValueWithLang(): array
     {
         return [
             'classic_field_lang_true' => [
                 1 => 'value_1',
                 2 => 'value_2',
             ]
         ];
     }

    public static function fieldValueWithoutLang(string $key, string $value): array
    {
        return [
            $key => $value,
        ];
    }
}
