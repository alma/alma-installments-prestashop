<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Validator;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\CurrencyException;
use PrestaShop\Module\Alma\Application\Validator\CurrencyValidator;
use PrestaShop\Module\Alma\Infrastructure\Proxy\CurrencyProxy;

class CurrencyValidatorTest extends TestCase
{
    public function setUp(): void
    {
        $this->currencyProxy = $this->createMock(CurrencyProxy::class);
        $this->currencyValidator = new CurrencyValidator(
            $this->currencyProxy
        );
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\CurrencyException
     */
    public function testCheckCurrencyEurReturnVoid(): void
    {
        $this->currencyProxy->expects($this->once())
            ->method('getCurrencyIsoCode')
            ->with(1)
            ->willReturn('EUR');
        $this->assertNull($this->currencyValidator->checkCurrency(1));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\CurrencyException
     */
    public function testCheckCurrencyUSDThrowException(): void
    {
        $this->currencyProxy->expects($this->once())
            ->method('getCurrencyIsoCode')
            ->with(2)
            ->willReturn('USD');
        $this->expectException(CurrencyException::class);
        $this->currencyValidator->checkCurrency(2);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\CurrencyException
     */
    public function testCheckCurrencyIdDoesNotExistThrowException(): void
    {
        $this->currencyProxy->expects($this->once())
            ->method('getCurrencyIsoCode')
            ->with(3)
            ->willReturn('');
        $this->expectException(CurrencyException::class);
        $this->currencyValidator->checkCurrency(3);
    }
}
