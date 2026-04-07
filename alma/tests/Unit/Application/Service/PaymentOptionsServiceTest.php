<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Application\Service\PaymentOptionsService;
use PrestaShop\Module\Alma\Application\Validator\CurrencyValidator;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShopBundle\Translation\TranslatorInterface;

class PaymentOptionsServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->paymentOption = $this->createMock(PaymentOption::class);
        $this->currencyValidator = $this->createMock(CurrencyValidator::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->cart = $this->createMock(\Cart::class);
        $this->paymentOptionsService = new PaymentOptionsService(
            $this->module,
            $this->paymentOption,
            $this->currencyValidator,
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
}
