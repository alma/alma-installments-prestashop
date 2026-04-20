<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma;
use Alma\Client\Domain\Entity\EligibilityList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Application\Service\EligibilityService;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Application\Service\PaymentOptionsService;
use PrestaShop\Module\Alma\Application\Validator\CurrencyValidator;
use PrestaShop\Module\Alma\Tests\Mocks\EligibilityMock;
use PrestaShop\Module\Alma\Tests\Mocks\PaymentOptionMock;
use PrestaShop\Module\Alma\Tests\Mocks\ProductMock;

class PaymentOptionsServiceTest extends TestCase
{
    /**
     * @var ExcludedCategoriesService
     */
    private $excludedCategoriesService;
    /**
     * @var PaymentOptionsService
     */
    private PaymentOptionsService $paymentOptionsService;
    /**
     * @var EligibilityService
     */
    private $eligibilityService;
    /**
     * @var Alma
     */
    private $module;

    public function setUp(): void
    {
        $this->module = $this->createMock(\Alma::class);
        $this->module->name = 'alma';
        $this->currencyValidator = $this->createMock(CurrencyValidator::class);
        $this->excludedCategoriesService = $this->createMock(ExcludedCategoriesService::class);
        $this->eligibilityService = $this->createMock(EligibilityService::class);
        $this->cart = $this->createMock(\Cart::class);
        $this->paymentOptionsService = new PaymentOptionsService(
            $this->module,
            $this->currencyValidator,
            $this->excludedCategoriesService,
            $this->eligibilityService
        );
    }

    public function testBuildPaymentOptionsReturnEmptyArrayIfCurrencyNotSupported(): void
    {
        $this->cart->id_currency = 2;
        $this->paymentOptionsService->setCart($this->cart);
        $this->currencyValidator->expects($this->once())
            ->method('checkCurrency')
            ->with(2)
            ->willThrowException(new CurrencyException('Currency not supported'));
        $this->assertEquals([], $this->paymentOptionsService->buildPaymentOptions());
    }

    public function testBuildPaymentOptionsReturnEmptyArrayIfProductInCartIsExcludedCategories(): void
    {
        $products = [
            ProductMock::productArray(),
        ];
        $this->cart->id_currency = 1;
        $this->paymentOptionsService->setCart($this->cart);
        $this->currencyValidator->expects($this->once())
            ->method('checkCurrency')
            ->with(1);
        $this->cart->expects($this->once())
            ->method('getProducts')
            ->willReturn($products);
        $this->excludedCategoriesService->expects($this->once())
            ->method('isExcluded')
            ->with($products)
            ->willReturn(true);
        $this->assertEquals([], $this->paymentOptionsService->buildPaymentOptions());
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testBuildPaymentOptionsWithFeePlanReturnPaymentOption(): void
    {
        $paymentOption = PaymentOptionMock::paymentOption();
        $products = [
            ProductMock::productArray(),
        ];
        $eligibilityList = new EligibilityList();
        $eligibilityList->add(EligibilityMock::eligibility(2));

        $this->cart->id_currency = 1;
        $this->paymentOptionsService->setCart($this->cart);
        $this->currencyValidator->expects($this->once())
            ->method('checkCurrency')
            ->with(1);
        $this->cart->expects($this->once())
            ->method('getProducts')
            ->willReturn($products);
        $this->excludedCategoriesService->expects($this->once())
            ->method('isExcluded')
            ->with($products)
            ->willReturn(false);
        $this->eligibilityService->expects($this->once())
            ->method('getEligibilityForCheckout')
            ->with($this->cart)
            ->willReturn($eligibilityList);
        $this->module->expects($this->once())
            ->method('translate')
            ->with('Pay with Alma', [], 'Modules.Alma.Checkout')
            ->willReturn('Pay with Alma');
        $this->assertEquals([$paymentOption], $this->paymentOptionsService->buildPaymentOptions());
    }
}
