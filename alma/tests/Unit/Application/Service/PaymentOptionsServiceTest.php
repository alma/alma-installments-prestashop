<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Application\Service\PaymentOptionsService;
use PrestaShop\Module\Alma\Application\Validator\CurrencyValidator;
use PrestaShop\Module\Alma\Tests\Mocks\ProductMock;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShopBundle\Translation\TranslatorInterface;

class PaymentOptionsServiceTest extends TestCase
{
    /**
     * @var ExcludedCategoriesService
     */
    private $excludedCategoriesService;

    public function setUp(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->paymentOption = $this->createMock(PaymentOption::class);
        $this->currencyValidator = $this->createMock(CurrencyValidator::class);
        $this->excludedCategoriesService = $this->createMock(ExcludedCategoriesService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->cart = $this->createMock(\Cart::class);
        $this->paymentOptionsService = new PaymentOptionsService(
            $this->module,
            $this->paymentOption,
            $this->currencyValidator,
            $this->excludedCategoriesService,
            $this->translator,
            $this->cart
        );
    }

    public function testBuildPaymentOptionsReturnEmptyArrayIfCurrencyNotSupported(): void
    {
        $this->cart->id_currency = 2;
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
}
