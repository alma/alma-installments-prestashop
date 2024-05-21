<?php
/**
 * 2018-2023 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\PrestaShop\Builders\CustomFieldHelperBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\LanguageHelper;
use PHPUnit\Framework\TestCase;

class CustomFieldHelperTest extends TestCase
{
    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    /**
     * @var string[]
     */
    protected static $data = [
        'ALMA_PAY_NOW_BUTTON_TITLE' => 'Pay now by credit card',
        'ALMA_PAY_NOW_BUTTON_DESC' => 'Fast and secure payments.',
        'ALMA_PNX_BUTTON_TITLE' => 'Pay in %d installments',
        'ALMA_PNX_BUTTON_DESC' => 'Fast and secure payment by credit card.',
        'ALMA_DEFERRED_BUTTON_TITLE' => 'Buy now Pay in %d days',
        'ALMA_DEFERRED_BUTTON_DESC' => 'Fast and secure payment by credit card.',
        'ALMA_PNX_AIR_BUTTON_TITLE' => 'Pay in %d installments',
        'ALMA_PNX_AIR_BUTTON_DESC' => 'Fast and secure payment by credit card.',
        'ALMA_NOT_ELIGIBLE_CATEGORIES' => 'Your cart is not eligible for payments with Alma.',
        'ALMA_DESCRIPTION_TRIGGER' => 'At shipping',
    ];

    public function setUp()
    {
        $customFieldHelperBuilder = new CustomFieldHelperBuilder();
        $this->customFieldsHelper = $customFieldHelperBuilder->getInstance();
    }

    public function testCustomFields()
    {
        $result = $this->customFieldsHelper->customFields();

        $this->assertEquals(static::$data, $result);
    }

    /**
     * Test testGetAllLangCustomFieldByKeyConfig
     *
     * @dataProvider provideAllLangCustomFieldByKeyConfig
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function testGetAllLangCustomFieldByKeyConfig($key, $value)
    {
        $result = $this->customFieldsHelper->getAllLangCustomFieldByKeyConfig($key, [
            [
                'id_lang' => '1',
                'iso_code' => 'en',
            ],
        ]);

        $this->assertEquals([
            '1' => [
                'locale' => 'en',
                'string' => $value,
            ],
        ], $result);
    }

    /**
     * @return array[]
     */
    public function provideAllLangCustomFieldByKeyConfig()
    {
        return [
             'test ALMA_PAY_NOW_BUTTON_TITLE' => [
                 'key' => 'ALMA_PAY_NOW_BUTTON_TITLE',
                 'value' => 'Pay now by credit card',
             ],
             'test ALMA_PAY_NOW_BUTTON_DESC' => [
                'key' => 'ALMA_PAY_NOW_BUTTON_DESC',
                'value' => 'Fast and secure payments.',
             ],
            'test ALMA_PNX_BUTTON_TITLE' => [
                'key' => 'ALMA_PNX_BUTTON_TITLE',
                'value' => 'Pay in %d installments',
            ],
            'test ALMA_PNX_BUTTON_DESC' => [
                'key' => 'ALMA_PNX_BUTTON_DESC',
                'value' => 'Fast and secure payment by credit card.',
            ],
            'test ALMA_DEFERRED_BUTTON_TITLE' => [
                'key' => 'ALMA_PNX_BUTTON_DESC',
                'value' => 'Fast and secure payment by credit card.',
            ],
           'test ALMA_PNX_AIR_BUTTON_TITLE' => [
                'key' => 'ALMA_PNX_AIR_BUTTON_TITLE',
                'value' => 'Pay in %d installments',
            ],
            'test ALMA_PNX_AIR_BUTTON_DESC' => [
                'key' => 'ALMA_PNX_AIR_BUTTON_DESC',
                'value' => 'Fast and secure payment by credit card.',
            ],
            'test ALMA_NOT_ELIGIBLE_CATEGORIES' => [
                'key' => 'ALMA_NOT_ELIGIBLE_CATEGORIES',
                'value' => 'Your cart is not eligible for payments with Alma.',
            ],
            'test ALMA_DESCRIPTION_TRIGGER' => [
                'key' => 'ALMA_DESCRIPTION_TRIGGER',
                'value' => 'At shipping',
            ],
        ];
    }

    /**
     * @dataProvider provideAllLangCustomFieldByKeyConfig
     *
     * @return void
     */
    public function testGetCustomFieldByKeyConfig($key, $value)
    {
        $languageHelperMock = $this->createMock(LanguageHelper::class);
        $languageHelperMock->method('getLanguages')->willReturn([
            [
                'id_lang' => '1',
                'iso_code' => 'en',
            ],
        ]);
        $result = $this->customFieldsHelper->getCustomFieldByKeyConfig($key, $languageHelperMock->getLanguages());

        $this->assertEquals(['1' => $value], $result);
    }

    /**
     * @dataProvider provideAllLangCustomFieldByKeyConfig
     *
     * @return void
     */
    public function testGetValue($key, $value)
    {
        $result = $this->customFieldsHelper->getValue($key);
        $this->assertEquals(['1' => $value], $result);
    }

    /**
     * @dataProvider provideAllLangCustomFieldByKeyConfig
     *
     * @return void
     */
    public function testGetBtnValueByLang($key, $value)
    {
        $result = $this->customFieldsHelper->getBtnValueByLang('1', $key);
        $this->assertEquals($value, $result);
    }

    public function testGetDescriptionPaymentTrigger()
    {
        $result = $this->customFieldsHelper->getDescriptionPaymentTrigger();
        $this->assertEquals(['1' => 'At shipping'], $result);
    }

    public function testGetDescriptionPaymentTriggerByLang()
    {
        $result = $this->customFieldsHelper->getDescriptionPaymentTriggerByLang('1');
        $this->assertEquals('At shipping', $result);
    }

    public function testGetTextButton()
    {
        $result = $this->customFieldsHelper->getTextButton(
            '1',
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE,
            15
        );

        $this->assertEquals('Buy now Pay in 15 days', $result);
    }

    public function testInitCustomFields()
    {
        $this->assertEquals(static::$data, $this->customFieldsHelper->customFields());
    }
}
