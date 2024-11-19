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

use Alma\PrestaShop\Exceptions\ValidateException;
use Alma\PrestaShop\Helpers\ValidateHelper;
use PHPUnit\Framework\TestCase;

class ValidateHelperTest extends TestCase
{
    const API_KEY = 'api_key_test';
    const EXTERNAL_ID = 'merchant_id_test';
    const WRONG_SIGNATURE = 'wrong_signature';
    const GOOD_SIGNATURE = '0dd3cb4632c074ead0d0f346c75015c76ad4e1e115f01c7e0850dd5accb7b4b0';
    /**
     * @var ValidateHelper
     */
    protected $validateHelper;

    public function setUp()
    {
        $this->validateHelper = new ValidateHelper();
    }

    public function tearDown()
    {
        $this->validateHelper = null;
    }

    /**
     * @dataProvider checkSignatureWrongParamsDataProvider
     *
     * @throws ValidateException
     */
    public function testCheckSignatureWithoutParamsReturnError($externalId, $apiKey, $signature)
    {
        $this->expectException(ValidateException::class);
        $this->validateHelper->checkSignature($externalId, $apiKey, $signature);
    }

    /**
     * @throws ValidateException
     */
    public function testCheckSignatureWithBadSignatureReturnError()
    {
        $this->expectException(ValidateException::class);
        $this->validateHelper->checkSignature(self::EXTERNAL_ID, self::API_KEY, self::WRONG_SIGNATURE);
    }

    /**
     * @throws ValidateException
     */
    public function testCheckSignatureWithGoodSignature()
    {
        $this->validateHelper->checkSignature(self::EXTERNAL_ID, self::API_KEY, self::GOOD_SIGNATURE);
    }

    /**
     * @return array[]
     */
    public function checkSignatureWrongParamsDataProvider()
    {
        return [
            'Without api key' => [self::EXTERNAL_ID, '', self::GOOD_SIGNATURE],
            'Without payement id' => ['', self::API_KEY, self::GOOD_SIGNATURE],
            'Without signature' => [self::EXTERNAL_ID, self::API_KEY, ''],
            'With api key null' => [self::EXTERNAL_ID, null, self::GOOD_SIGNATURE],
            'With payement id null' => [null, self::API_KEY, self::GOOD_SIGNATURE],
            'With signature null' => [self::EXTERNAL_ID, self::API_KEY, null],
        ];
    }
}
