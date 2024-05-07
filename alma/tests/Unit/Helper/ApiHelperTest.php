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

use Alma\PrestaShop\Builders\ApiHelperBuilder;
use Alma\PrestaShop\Helpers\ClientHelper;
use Mockery;
use PHPUnit\Framework\TestCase;

class ApiHelperTest extends TestCase
{
    public function testGetMerchant()
    {
        $clientHelper = Mockery::mock(ClientHelper::class)->makePartial();
        $clientHelper->shouldReceive('getMerchantsMe')->andThrow(new \Exception('', 401));
        $apiHelperBuilder = Mockery::mock(ApiHelperBuilder::class);
        $apiHelperBuilder->shouldReceive('getClientHelper')->andReturn($clientHelper);

    }

    public function testHandleInsuranceFlag()
    {

    }

    public function testSaveFeatureFlag()
    {

    }
    public function testGetPaymentEligibility()
    {
    }
}
