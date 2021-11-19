<?php
namespace Tests\Unit\lib\Utils;

use Mockery;
use Language;
use PHPUnit\Framework\TestCase;
use Alma\PrestaShop\Utils\SettingsCustomFields;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

define('_PS_MODULE_DIR_', '../');
define('_PS_THEME_DIR_', '../');

class SettingsCustomFieldsTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    // public function testExemple()
    // {
    //     $array = [];

    //     $expected = 0;

    //     $this->assertSame($expected, count($array));
    // }

    // public function tearDown()
    // {
    //     Mockery::close();
    // }

    public function testGetCustomFieldButtonTitle2LangsFrEn()
    {
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

        $this->assertSame($expected, SettingsCustomFields::getAllLangCustomFieldByKeyConfig('ALMA_PAYMENT_BUTTON_TITLE', $languages));
    }

    public function testGetCustomFieldByKeyConfig()
    {
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

        $arrayShops = [
            [
                'id_shop' => '1',
                'id_shop_group' => '1',
                'name' => 'PrestaShop',
                'id_category' => '2',
                'theme_name' => 'classic',
                'active' => '1',
                'deleted' => '0',
            ]
        ];

        $mockLanguage = Mockery::mock('Language');
        $mockLanguage->shouldReceive('language')
            ->andReturn($arrayLanguages);
        $languages = $mockLanguage->language();

        $mockShop = Mockery::mock('Shop');
        $mockShop->shouldReceive('shop')
            ->andReturn($arrayShops);
        $shop = $mockShop->shop();

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

        $this->assertSame($expected, SettingsCustomFields::getCustomFieldByKeyConfig('ALMA_PAYMENT_BUTTON_TITLE', $languages));
    }

    // public function testGetPaymentButtonTitle()
    // {
    //     $expected = [
    //         1 => 'Payer en %d fois',
    //         2 => 'Pay in %d installments',
    //     ];

    //     $this->assertSame($expected, SettingsCustomFields::getPaymentButtonTitle());
    // }
}