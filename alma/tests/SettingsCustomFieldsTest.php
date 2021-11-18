<?php
use Alma\PrestaShop\Utils\SettingsCustomFields;

define('_PS_MODULE_DIR_', '../');
define('_PS_THEME_DIR_', '../');

test('getCustomFieldByKeyConfig2LangsFrEn', function() {
    $arrayLanguages = [
        [
            'id_lang' => '1',
            'name' => 'Français (French)',
            'active' => '1',
            'iso_code' => 'fr',
            'language_code' => 'fr',
            'locale' => 'fr-FR',
            'date_format_lite' => 'd/m/Y',
            'date_format_full' => 'd/m/Y H:i:s',
            'is_rtl' => '0',
            'id_shop' => '1',
            'shops' => [
                1 => true
            ],
        ],
        [
            'id_lang' => '2',
            'name' => 'English (English)',
            'active' => '1',
            'iso_code' => 'en',
            'language_code' => 'en-us',
            'locale' => 'en-US',
            'date_format_lite' => 'd/m/Y',
            'date_format_full' => 'd/m/Y H:i:s',
            'is_rtl' => '0',
            'id_shop' => '1',
            'shops' => [
                1 => true
            ],
        ]
    ];
    $mock = Mockery::mock('Language');
    $mock->shouldReceive('language')
        ->andReturn($arrayLanguages);
    $languages = $mock->language();

    $expected = [
        1 => [
            'locale' => 'fr',
            'string' => 'Payer en %d fois'
        ],
        2 => [
            'locale' => 'en',
            'string' => 'Pay in %d installments'
        ],
    ];

    $this->assertEquals(
        $expected,
        SettingsCustomFields::getAllLangCustomFieldByKeyConfig('ALMA_PAYMENT_BUTTON_TITLE', $languages)
    );
});

// test('getPaymentButtonTitle', function() {
//     $this->assertEquals([
//             1 => 'Payer en %d fois',
//             2 => 'Pay in %d installments',
//         ],
//         SettingsCustomFields::getPaymentButtonTitle()
//     );
// });