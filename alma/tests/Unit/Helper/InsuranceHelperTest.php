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

use Alma\PrestaShop\Exceptions\WrongParamsException;
use Alma\PrestaShop\Helpers\Admin\InsuranceHelper;
use Alma\PrestaShop\Helpers\Admin\TabsHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use PHPUnit\Framework\TestCase;

class InsuranceHelperTest extends TestCase
{
    /**
     * @property $tabWithId
     */
    protected $tabWithId;

    /**
     * @property $tabWithoutId
     */
    protected $tabWithoutId;

    /**
     * @property $configurationHelperMock
     */
    protected $configurationHelper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->module = $this->createMock(\Module::class);
        $this->tabWithId = new \Tab();
        $this->tabWithId->id = 1;
        $this->tabWithoutId = new \Tab();
        $this->tabHelper = $this->createMock(TabsHelper::class);
        $this->insuranceHelper = new InsuranceHelper();
        $this->insuranceHelper->tabsHelper = $this->tabHelper;
        $this->configurationHelperMock = $this->createMock(ConfigurationHelper::class);
        $this->insuranceHelper->configurationHelper = $this->configurationHelperMock;
    }

    protected function tearDown()
    {
        $this->tabHelper = null;
        $this->insuranceHelper = null;
    }

    /**
     * @dataProvider dataTabInsuranceDataProvider
     *
     * @param $moduleName
     * @param $class
     * @param $name
     * @param $parent
     * @param $position
     * @param $icon
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testTabInstalledWithAllowFlagAndTabNotInstalled($moduleName, $class, $name, $parent, $position, $icon)
    {
        $this->tabHelper->method('getInstanceFromClassName')->willReturn($this->tabWithoutId);
        $this->tabHelper->method('installTab');
        $this->tabHelper->expects($this->once())->method('installTab')->with(
            $moduleName,
            $class,
            $name,
            $parent,
            $position,
            $icon
        );

        $this->insuranceHelper->handleBOMenu($this->module, true);
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testTabUninstalledWithDisallowFlagAndTabInstalled()
    {
        $this->tabHelper->method('getInstanceFromClassName')->willReturn($this->tabWithId);
        $this->tabHelper->method('uninstallTab');
        $this->tabHelper->expects($this->once())->method('uninstallTab')->with(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);

        $this->insuranceHelper->handleBOMenu($this->module, false);
    }

    /**
     * @dataProvider isAllowInsuranceDataProvider
     *
     * @param $isAllowInsurance
     * @param $tab
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testTabMethodNotCalledWithWrongParams($isAllowInsurance, $tab)
    {
        $this->tabHelper->method('getInstanceFromClassName')->willReturn($tab);

        $this->tabHelper->expects($this->never())->method('installTab');
        $this->tabHelper->expects($this->never())->method('uninstallTab');

        $this->assertNull($this->insuranceHelper->handleBOMenu($this->module, $isAllowInsurance));
    }

    /**
     * @return void
     */
    public function testUpdateValueIfFlagIsAllowAndInsuranceIsDisabled()
    {
        $this->configurationHelperMock->method('hasKey')->willReturn(false);
        $this->configurationHelperMock->expects($this->exactly(4))->method('updateValue')->withConsecutive(
            [ConstantsHelper::ALMA_ACTIVATE_INSURANCE, 0],
            [ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, 0],
            [ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, 0],
            [ConstantsHelper::ALMA_SHOW_INSURANCE_POPUP_CART, 0]
        );
        $this->insuranceHelper->handleDefaultInsuranceFieldValues(true);
    }

    /**
     * @return void
     */
    public function testDeleteByNamesIfFlagIsDisallowAndInsuranceIsAllow()
    {
        $this->configurationHelperMock->method('hasKey')->willReturn(true);
        $this->configurationHelperMock->expects($this->once())->method('deleteByNames');
        $this->insuranceHelper->handleDefaultInsuranceFieldValues(false);
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testConstructIframeUrlWithParams()
    {
        $expected = 'https://protect.staging.almapay.com/almaBackOfficeConfiguration.html?is_insurance_activated=true&is_insurance_on_product_page_activated=false&is_insurance_on_cart_page_activated=false&is_add_to_cart_popup_insurance_activated=true';

        $this->configurationHelperMock->method('getMultiple')->willReturn([
            'ALMA_ACTIVATE_INSURANCE' => '1',
            'ALMA_SHOW_INSURANCE_WIDGET_PRODUCT' => '0',
            'ALMA_SHOW_INSURANCE_WIDGET_CART' => '0',
            'ALMA_SHOW_INSURANCE_POPUP_CART' => '1',
        ]);
        $actual = $this->insuranceHelper->constructIframeUrlWithParams();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\WrongParamsException
     */
    public function testSaveConfigInsurance()
    {
        $this->configurationHelperMock->method('updateValue');
        $this->configurationHelperMock->expects($this->exactly(4))->method('updateValue')->withConsecutive(
            [ConstantsHelper::ALMA_ACTIVATE_INSURANCE, 1],
            [ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT, 1],
            [ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART, 0],
            [ConstantsHelper::ALMA_SHOW_INSURANCE_POPUP_CART, 1]
        );
        $this->insuranceHelper->saveConfigInsurance([
            'is_insurance_activated' => '1',
            'is_insurance_on_product_page_activated' => '1',
            'is_insurance_on_cart_page_activated' => '0',
            'is_add_to_cart_popup_insurance_activated' => '1',
        ], $this->module);
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     *
     * @throws WrongParamsException
     */
    public function testDontSaveConfigInsuranceWithWrongKeysAndThrowException()
    {
        $this->configurationHelperMock->method('updateValue');
        $this->configurationHelperMock->expects($this->never())->method('updateValue');
        $this->expectException(WrongParamsException::class);
        $this->insuranceHelper->saveConfigInsurance([
            'is_insurance_activated' => '1',
            'is_insurance_on_product_page_activated' => '1',
            'is_insurance_on_cart_page_activated' => '0',
            'is_add_to_cart_popup_insurance_activated' => '1',
            'key_false' => '0',
        ], $this->module);
    }

    /**
     * @return array
     */
    public function isAllowInsuranceDataProvider()
    {
        $tabWithoutId = new \Tab();
        $tabWithId = new \Tab();
        $tabWithId->id = 1;

        return [
            'is allow' => [
                'isAllowInsurance' => true,
                'tab' => $tabWithId,
            ],
            'is disallow' => [
                'isDisallowInsurance' => false,
                'tab' => $tabWithoutId,
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataTabInsuranceDataProvider()
    {
        return [
            'install tab' => [
                'moduleName' => 'alma',
                'class' => 'AdminAlmaInsurance',
                'name' => null,
                'parent' => 'alma',
                'position' => 3,
                'icon' => 'security',
            ],
        ];
    }
}
