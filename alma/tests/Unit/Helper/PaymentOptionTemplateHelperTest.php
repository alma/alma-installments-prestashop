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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Builders\Helpers\PaymentOptionTemplateHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Helpers\TranslationHelper;
use PHPUnit\Framework\TestCase;

class PaymentOptionTemplateHelperTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\PaymentOptionTemplateHelper
     */
    protected $paymentOptionTemplateHelper;

    public function setUp()
    {
        $paymentOptionTemplateHelperBuilder = new PaymentOptionTemplateHelperBuilder();
        $this->paymentOptionTemplateHelper = $paymentOptionTemplateHelperBuilder->getInstance();

        $this->settingsHelperMock = \Mockery::mock(SettingsHelper::class);
        $this->configurationHelperMock = \Mockery::mock(ConfigurationHelper::class);
        $this->translationHelperMock = \Mockery::mock(TranslationHelper::class);
        $this->priceHelperMock = \Mockery::mock(PriceHelper::class);
        $this->dateHelperMock = \Mockery::mock(DateHelper::class);
        $this->contextFactoryMock = \Mockery::mock(ContextFactory::class);
        $this->moduleFactoryMock = \Mockery::mock(ModuleFactory::class);
    }

    public function testGetTemplateAndBNPL()
    {
        $this->assertEquals(
            ['payment_button_deferred.tpl', 10],
            $this->paymentOptionTemplateHelper->getTemplateAndBNPL(0, 10, 2)
        );

        $this->assertEquals(
            ['payment_button_pnx.tpl', 4],
            $this->paymentOptionTemplateHelper->getTemplateAndBNPL(4, 0, 0)
        );
    }

    public function testBuildTemplateVarNoDeferredPlan()
    {
        $plan = \Mockery::mock(FeePlan::class);
        $plan->customerTotalCostAmount = 100;
        $plan->annualInterestRate = 1;
        $plan->installmentsCount = 1;
        $plan->deferredDays = 5;
        $plan->deferredMonths = 1;

        $plans = [
            [
                'purchase_amount' => 100,
                'customer_fee' => 10,
                'due_date' => '2024-05-22',
            ],
        ];

        $this->settingsHelperMock->shouldReceive('getModeActive')->andReturn('test');
        $this->settingsHelperMock->shouldReceive('getIdMerchant')->andReturn('merchantId');
        $this->configurationHelperMock->shouldReceive('isInPageEnabled')->andReturn(true);
        $this->translationHelperMock->shouldReceive('getTranslation')->andReturn('My translation');
        $this->priceHelperMock->shouldReceive('formatPriceToCentsByCurrencyId')->andReturn('110.00');
        $this->dateHelperMock->shouldReceive('getDateFormat')->andReturn('22/05/2024');

        $paymentOptionTemplateHelper = new PaymentOptionTemplateHelper(
            new ContextFactory(),
            new ModuleFactory(new ToolsHelper()),
            $this->settingsHelperMock,
            $this->configurationHelperMock,
            $this->translationHelperMock,
            $this->priceHelperMock,
            $this->dateHelperMock
        );

        $result = $paymentOptionTemplateHelper->buildTemplateVar(
            'general_1_0_0',
            'update',
            'description',
            $plans,
            0,
            $plan,
            1,
            'en',
            100,
            false
        );

        $expectedResult = [
            'keyPlan' => 'general_1_0_0',
            'action' => 'update',
            'desc' => 'description',
            'plans' => $plans,
            'deferred_trigger_limit_days' => 0,
            'apiMode' => 'TEST',
            'merchantId' => 'merchantId',
            'isInPageEnabled' => true,
            'first' => 1,
            'creditInfo' => [
                'totalCart' => 100,
                'costCredit' => 100,
                'totalCredit' => 200,
                'taeg' => 1,
            ],
            'installment' => 1,
            'deferredDays' => 5,
            'deferredMonths' => 1,
            'locale' => 'en',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testBuildTemplateVarDeferredPlan()
    {
        $plan = \Mockery::mock(FeePlan::class);
        $plan->customerTotalCostAmount = 100;
        $plan->annualInterestRate = 1;
        $plan->installmentsCount = 1;
        $plan->deferredDays = 5;
        $plan->deferredMonths = 1;

        $plans = [
            [
                'purchase_amount' => 100,
                'customer_fee' => 10,
                'due_date' => '2024-05-22',
            ],
        ];

        $this->settingsHelperMock->shouldReceive('getModeActive')->andReturn('test');
        $this->settingsHelperMock->shouldReceive('getIdMerchant')->andReturn('merchantId');
        $this->configurationHelperMock->shouldReceive('isInPageEnabled')->andReturn(true);
        $this->translationHelperMock->shouldReceive('getTranslation')->andReturn('My translation');
        $this->priceHelperMock->shouldReceive('formatPriceToCentsByCurrencyId')->andReturn('110.00');
        $this->dateHelperMock->shouldReceive('getDateFormat')->andReturn('22/05/2024');

        $paymentOptionTemplateHelper = new PaymentOptionTemplateHelper(
            new ContextFactory(),
            new ModuleFactory(new ToolsHelper()),
            $this->settingsHelperMock,
            $this->configurationHelperMock,
            $this->translationHelperMock,
            $this->priceHelperMock,
            $this->dateHelperMock
        );

        $result = $paymentOptionTemplateHelper->buildTemplateVar(
            'general_1_0_0',
            'update',
            'description',
            $plans,
            5,
            $plan,
            1,
            'en',
            100,
            true
        );

        $expectedResult = [
            'keyPlan' => 'general_1_0_0',
            'action' => 'update',
            'desc' => 'description',
            'plans' => $plans,
            'deferred_trigger_limit_days' => 5,
            'apiMode' => 'TEST',
            'merchantId' => 'merchantId',
            'isInPageEnabled' => true,
            'first' => 1,
            'creditInfo' => [
                'totalCart' => 100,
                'costCredit' => 100,
                'totalCredit' => 200,
                'taeg' => 1,
            ],
            'installment' => 1,
            'deferredDays' => 5,
            'deferredMonths' => 1,
            'locale' => 'en',
            'installmentText' => 'My translation',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTemplateInPage()
    {
        $smartyMock = \Mockery::mock(\Smarty::class);
        $smartyMock->shouldReceive('fetch')->andReturn('module:alma/views/templates/front/payment_form_inpage.tpl');

        $contextMock = \Mockery::mock(\Context::class);
        $contextMock->smarty = $smartyMock;

        $this->contextFactoryMock->shouldReceive('getContext')->andReturn($contextMock);

        $moduleMock = \Mockery::mock(\Module::class);
        $moduleMock->name = 'alma';
        $this->moduleFactoryMock->shouldReceive('getModule')->andReturn($moduleMock);

        $paymentOptionTemplateHelper = new PaymentOptionTemplateHelper(
            $this->contextFactoryMock,
            $this->moduleFactoryMock,
            $this->settingsHelperMock,
            $this->configurationHelperMock,
            $this->translationHelperMock,
            $this->priceHelperMock,
            $this->dateHelperMock
        );

        $this->assertEquals(
            'module:alma/views/templates/front/payment_form_inpage.tpl',
            $paymentOptionTemplateHelper->getTemplateInPage()
        );
    }

    public function testBuildSmartyTemplate()
    {
        $smartyMock = \Mockery::mock(\Smarty::class);
        $smartyMock->shouldReceive('fetch')->andReturn('module:alma/views/templates/front/payment_form_inpage.tpl');
        $smartyMock->shouldReceive('assign')->andReturn();

        $contextMock = \Mockery::mock(\Context::class);
        $contextMock->smarty = $smartyMock;

        $this->contextFactoryMock->shouldReceive('getContext')->andReturn($contextMock);

        $moduleMock = \Mockery::mock(\Module::class);
        $moduleMock->name = 'alma';
        $this->moduleFactoryMock->shouldReceive('getModule')->andReturn($moduleMock);

        $paymentOptionTemplateHelper = new PaymentOptionTemplateHelper(
            $this->contextFactoryMock,
            $this->moduleFactoryMock,
            $this->settingsHelperMock,
            $this->configurationHelperMock,
            $this->translationHelperMock,
            $this->priceHelperMock,
            $this->dateHelperMock
        );

        $this->assertEquals(
            'module:alma/views/templates/front/payment_form_inpage.tpl',
            $paymentOptionTemplateHelper->buildSmartyTemplate([], 'payment_form_inpage.tpl')
        );
    }

    public function tearDown()
    {
        $this->paymentOptionTemplateHelper = null;
        $this->settingsHelperMock = null;
        $this->configurationHelperMock = null;
        $this->translationHelperMock = null;
        $this->priceHelperMock = null;
        $this->dateHelperMock = null;
    }
}
