<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Exceptions\MissingParameterException;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
use Alma\PrestaShop\Services\CustomFieldsFormService;
use PHPUnit\Framework\TestCase;

class CustomFieldsFormServiceTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    private $configurationProxyMock;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    private $toolsProxyMock;

    public function setUp()
    {
        $this->contextMock = $this->createMock(\Context::class);
        $this->controllerMock = $this->createMock(\AdminController::class);
        $this->contextMock->controller = $this->controllerMock;
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);
        $this->configurationProxyMock = $this->createMock(ConfigurationProxy::class);
        $this->customFieldsFormService = new CustomFieldsFormService(
            $this->contextMock,
            $this->toolsProxyMock,
            $this->configurationProxyMock
        );
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     */
    public function testSaveWithOneLanguageOnPS17WithEmptyFieldInPnxButtonTitleThrowMissingParameterException()
    {
        $languages = [
            [
                'id_lang' => '1',
                'name' => 'English (English)',
                'active' => '1',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'locale' => 'en-US',
                'date_format_lite' => 'm/d/Y',
                'date_format_full' => 'm/d/Y H:i:s',
                'is_rtl' => '0',
                'id_shop' => '1',
                'shops' => [
                    1 => true,
                ],
                'is_default' => 1,
            ],
        ];
        $this->controllerMock->expects($this->once())
            ->method('getLanguages')
            ->willReturn($languages);

        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['ALMA_PAY_NOW_BUTTON_TITLE_1'], ['ALMA_PNX_BUTTON_TITLE_1'])
            ->willReturnOnConsecutiveCalls('Test Custom Field', '');
        $this->expectException(MissingParameterException::class);
        $this->customFieldsFormService->save();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     */
    public function testSaveWithOneLanguageOnPS17WithoutEmptyField()
    {
        $languages = [
            [
                'id_lang' => '1',
                'name' => 'English (English)',
                'active' => '1',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'locale' => 'en-US',
                'date_format_lite' => 'm/d/Y',
                'date_format_full' => 'm/d/Y H:i:s',
                'is_rtl' => '0',
                'id_shop' => '1',
                'shops' => [
                    1 => true,
                ],
                'is_default' => 1,
            ],
        ];
        $this->controllerMock->expects($this->once())
            ->method('getLanguages')
            ->willReturn($languages);

        $this->toolsProxyMock->expects($this->exactly(9))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_PAY_NOW_BUTTON_TITLE_1'],
                ['ALMA_PNX_BUTTON_TITLE_1'],
                ['ALMA_DEFERRED_BUTTON_TITLE_1'],
                ['ALMA_PNX_AIR_BUTTON_TITLE_1'],
                ['ALMA_PAY_NOW_BUTTON_DESC_1'],
                ['ALMA_PNX_BUTTON_DESC_1'],
                ['ALMA_DEFERRED_BUTTON_DESC_1'],
                ['ALMA_PNX_AIR_BUTTON_DESC_1'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES_1']
            )
            ->willReturnOnConsecutiveCalls(
                'Pay Now Custom Field Title',
                'Pnx Custom Field Title',
                'Deferred Custom Field Title',
                'Pnx air Custom Field Title',
                'Pay now Custom Field Desc',
                'Pnx Custom Field Desc',
                'Deferred Custom Field Desc',
                'Pnx air Custom Field Desc',
                'Not eligible categories Custom Field'
            );

        $this->configurationProxyMock->expects($this->exactly(9))
            ->method('updateValue')
            ->withConsecutive(
                ['ALMA_PNX_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Pnx Custom Field Title"}}'],
                ['ALMA_DEFERRED_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Deferred Custom Field Title"}}'],
                ['ALMA_PNX_AIR_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Pnx air Custom Field Title"}}'],
                ['ALMA_PAY_NOW_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Pay Now Custom Field Title"}}'],
                ['ALMA_PNX_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Pnx Custom Field Desc"}}'],
                ['ALMA_DEFERRED_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Deferred Custom Field Desc"}}'],
                ['ALMA_PNX_AIR_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Pnx air Custom Field Desc"}}'],
                ['ALMA_PAY_NOW_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Pay now Custom Field Desc"}}'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', '{"1":{"locale":"en-US","string":"Not eligible categories Custom Field"}}']
            );
        $this->customFieldsFormService->save();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     */
    public function testSaveWithTwoLanguagesOnPS17WithoutEmptyField()
    {
        $languages = [
            [
                'id_lang' => '1',
                'name' => 'English (English)',
                'active' => '1',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'locale' => 'en-US',
                'date_format_lite' => 'm/d/Y',
                'date_format_full' => 'm/d/Y H:i:s',
                'is_rtl' => '0',
                'id_shop' => '1',
                'shops' => [
                    1 => true,
                ],
                'is_default' => 1,
            ],
            [
                'id_lang' => '2',
                'name' => 'French (French)',
                'active' => '1',
                'iso_code' => 'fr',
                'language_code' => 'fr-fr',
                'locale' => 'fr-FR',
                'date_format_lite' => 'm/d/Y',
                'date_format_full' => 'm/d/Y H:i:s',
                'is_rtl' => '0',
                'id_shop' => '1',
                'shops' => [
                    1 => true,
                ],
                'is_default' => 1,
            ],
        ];
        $this->controllerMock->expects($this->once())
            ->method('getLanguages')
            ->willReturn($languages);

        $this->toolsProxyMock->expects($this->exactly(18))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_PAY_NOW_BUTTON_TITLE_1'],
                ['ALMA_PNX_BUTTON_TITLE_1'],
                ['ALMA_DEFERRED_BUTTON_TITLE_1'],
                ['ALMA_PNX_AIR_BUTTON_TITLE_1'],
                ['ALMA_PAY_NOW_BUTTON_DESC_1'],
                ['ALMA_PNX_BUTTON_DESC_1'],
                ['ALMA_DEFERRED_BUTTON_DESC_1'],
                ['ALMA_PNX_AIR_BUTTON_DESC_1'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES_1'],
                ['ALMA_PAY_NOW_BUTTON_TITLE_2'],
                ['ALMA_PNX_BUTTON_TITLE_2'],
                ['ALMA_DEFERRED_BUTTON_TITLE_2'],
                ['ALMA_PNX_AIR_BUTTON_TITLE_2'],
                ['ALMA_PAY_NOW_BUTTON_DESC_2'],
                ['ALMA_PNX_BUTTON_DESC_2'],
                ['ALMA_DEFERRED_BUTTON_DESC_2'],
                ['ALMA_PNX_AIR_BUTTON_DESC_2'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES_2']
            )
            ->willReturnOnConsecutiveCalls(
                'Pay Now Custom Field Title',
                'Pnx Custom Field Title',
                'Deferred Custom Field Title',
                'Pnx air Custom Field Title',
                'Pay now Custom Field Desc',
                'Pnx Custom Field Desc',
                'Deferred Custom Field Desc',
                'Pnx air Custom Field Desc',
                'Not eligible categories Custom Field',
                'Pay Now Custom Field Title FR',
                'Pnx Custom Field Title FR',
                'Deferred Custom Field Title FR',
                'Pnx air Custom Field Title FR',
                'Pay now Custom Field Desc FR',
                'Pnx Custom Field Desc FR',
                'Deferred Custom Field Desc FR',
                'Pnx air Custom Field Desc FR',
                'Not eligible categories Custom Field FR'
            );

        $this->configurationProxyMock->expects($this->exactly(9))
            ->method('updateValue')
            ->withConsecutive(
                ['ALMA_PNX_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Pnx Custom Field Title"},"2":{"locale":"fr-FR","string":"Pnx Custom Field Title FR"}}'],
                ['ALMA_DEFERRED_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Deferred Custom Field Title"},"2":{"locale":"fr-FR","string":"Deferred Custom Field Title FR"}}'],
                ['ALMA_PNX_AIR_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Pnx air Custom Field Title"},"2":{"locale":"fr-FR","string":"Pnx air Custom Field Title FR"}}'],
                ['ALMA_PAY_NOW_BUTTON_TITLE', '{"1":{"locale":"en-US","string":"Pay Now Custom Field Title"},"2":{"locale":"fr-FR","string":"Pay Now Custom Field Title FR"}}'],
                ['ALMA_PNX_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Pnx Custom Field Desc"},"2":{"locale":"fr-FR","string":"Pnx Custom Field Desc FR"}}'],
                ['ALMA_DEFERRED_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Deferred Custom Field Desc"},"2":{"locale":"fr-FR","string":"Deferred Custom Field Desc FR"}}'],
                ['ALMA_PNX_AIR_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Pnx air Custom Field Desc"},"2":{"locale":"fr-FR","string":"Pnx air Custom Field Desc FR"}}'],
                ['ALMA_PAY_NOW_BUTTON_DESC', '{"1":{"locale":"en-US","string":"Pay now Custom Field Desc"},"2":{"locale":"fr-FR","string":"Pay now Custom Field Desc FR"}}'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', '{"1":{"locale":"en-US","string":"Not eligible categories Custom Field"},"2":{"locale":"fr-FR","string":"Not eligible categories Custom Field FR"}}']
            );
        $this->customFieldsFormService->save();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     */
    public function testSaveWithOneLanguageOnPS16WithoutEmptyField()
    {
        $languages = [
            [
                'id_lang' => '1',
                'name' => 'English (English)',
                'active' => '1',
                'iso_code' => 'en',
                'language_code' => 'en-us',
                'date_format_lite' => 'm/d/Y',
                'date_format_full' => 'm/d/Y H:i:s',
                'is_rtl' => '0',
                'id_shop' => '1',
                'shops' => [
                    1 => true,
                ],
                'is_default' => 1,
            ],
        ];
        $this->controllerMock->expects($this->once())
            ->method('getLanguages')
            ->willReturn($languages);

        $this->toolsProxyMock->expects($this->exactly(9))
            ->method('getValue')
            ->withConsecutive(
                ['ALMA_PAY_NOW_BUTTON_TITLE_1'],
                ['ALMA_PNX_BUTTON_TITLE_1'],
                ['ALMA_DEFERRED_BUTTON_TITLE_1'],
                ['ALMA_PNX_AIR_BUTTON_TITLE_1'],
                ['ALMA_PAY_NOW_BUTTON_DESC_1'],
                ['ALMA_PNX_BUTTON_DESC_1'],
                ['ALMA_DEFERRED_BUTTON_DESC_1'],
                ['ALMA_PNX_AIR_BUTTON_DESC_1'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES_1']
            )
            ->willReturnOnConsecutiveCalls(
                'Pay Now Custom Field Title',
                'Pnx Custom Field Title',
                'Deferred Custom Field Title',
                'Pnx air Custom Field Title',
                'Pay now Custom Field Desc',
                'Pnx Custom Field Desc',
                'Deferred Custom Field Desc',
                'Pnx air Custom Field Desc',
                'Not eligible categories Custom Field'
            );

        $this->configurationProxyMock->expects($this->exactly(9))
            ->method('updateValue')
            ->withConsecutive(
                ['ALMA_PNX_BUTTON_TITLE', '{"1":{"locale":"en","string":"Pnx Custom Field Title"}}'],
                ['ALMA_DEFERRED_BUTTON_TITLE', '{"1":{"locale":"en","string":"Deferred Custom Field Title"}}'],
                ['ALMA_PNX_AIR_BUTTON_TITLE', '{"1":{"locale":"en","string":"Pnx air Custom Field Title"}}'],
                ['ALMA_PAY_NOW_BUTTON_TITLE', '{"1":{"locale":"en","string":"Pay Now Custom Field Title"}}'],
                ['ALMA_PNX_BUTTON_DESC', '{"1":{"locale":"en","string":"Pnx Custom Field Desc"}}'],
                ['ALMA_DEFERRED_BUTTON_DESC', '{"1":{"locale":"en","string":"Deferred Custom Field Desc"}}'],
                ['ALMA_PNX_AIR_BUTTON_DESC', '{"1":{"locale":"en","string":"Pnx air Custom Field Desc"}}'],
                ['ALMA_PAY_NOW_BUTTON_DESC', '{"1":{"locale":"en","string":"Pay now Custom Field Desc"}}'],
                ['ALMA_NOT_ELIGIBLE_CATEGORIES', '{"1":{"locale":"en","string":"Not eligible categories Custom Field"}}']
            );
        $this->customFieldsFormService->save();
    }
}
