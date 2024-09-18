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

namespace Alma\PrestaShop\Tests\Unit\Validators;

use Alma\API\Lib\PaymentValidator;
use Alma\PrestaShop\Exceptions\PaymentValidationException;
use Alma\PrestaShop\Validators\PaymentValidation;
use PHPUnit\Framework\TestCase;

class PaymentValidationTest extends TestCase
{
    const API_KEY = 'api_test_abc123';
    const PAYMENT_ID = 'payment_abc123';
    const WRONG_SIGNATURE = 'wrong_signature';
    const GOOD_SIGNATURE = 'good_signature';
    /**
     * @var PaymentValidation
     */
    protected $paymentValidation;
    /**
     * @var PaymentValidator
     */
    protected $clientPaymentValidator;

    public function setUp()
    {
        $this->clientPaymentValidator = $this->createMock(PaymentValidator::class);
        $this->paymentValidation = new PaymentValidation(
            $this->createMock(\Context::class),
            $this->createMock(\Module::class),
            $this->clientPaymentValidator
        );
    }

    public function tearDown()
    {
        $this->paymentValidation = null;
    }

    /**
     * @dataProvider checkSignatureWrongParamsDataProvider
     *
     * @throws PaymentValidationException
     */
    public function testCheckSignatureWithoutParamsReturnError($paymentId, $apiKey, $signature)
    {
        $this->expectException(PaymentValidationException::class);
        $this->paymentValidation->checkSignature($paymentId, $apiKey, $signature);
    }

    /**
     * @throws PaymentValidationException
     */
    public function testCheckSignatureWithBadSignatureReturnError()
    {
        $this->clientPaymentValidator->expects($this->once())
            ->method('isHmacValidated')
            ->with(self::PAYMENT_ID, self::API_KEY, self::WRONG_SIGNATURE)
            ->willReturn(false);
        $this->expectException(PaymentValidationException::class);
        $this->paymentValidation->checkSignature(self::PAYMENT_ID, self::API_KEY, self::WRONG_SIGNATURE);
    }

    /**
     * @throws PaymentValidationException
     */
    public function testCheckSignatureWithGoodSignatureReturnTrue()
    {
        $this->clientPaymentValidator->expects($this->once())
            ->method('isHmacValidated')
            ->with(self::PAYMENT_ID, self::API_KEY, self::GOOD_SIGNATURE)
            ->willReturn(true);
        $this->paymentValidation->checkSignature(self::PAYMENT_ID, self::API_KEY, self::GOOD_SIGNATURE);
    }

    /**
     * @return array[]
     */
    public function checkSignatureWrongParamsDataProvider()
    {
        return [
            'Without api key' => [self::PAYMENT_ID, '', self::GOOD_SIGNATURE],
            'Without payement id' => ['', self::API_KEY, self::GOOD_SIGNATURE],
            'Without signature' => [self::PAYMENT_ID, self::API_KEY, ''],
        ];
    }
}
