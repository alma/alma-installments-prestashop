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

use Alma\PrestaShop\Builders\Helpers\CarrierHelperBuilder;
use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Model\CarrierData;
use PHPUnit\Framework\TestCase;

class CarrierHelperTest extends TestCase
{
    public function testGetParentCarrierNameById()
    {
        $carrierHelperBuilder = new CarrierHelperBuilder();
        $carrierHelper = $carrierHelperBuilder->getInstance();

        $this->assertEquals('PrestaShop', $carrierHelper->getParentCarrierNameById(1));

        $carrierData = \Mockery::mock(CarrierData::class)->makePartial();
        $carrierData->shouldReceive('getCarriers')->andThrow(new \PrestaShopDatabaseException());

        $carrierHelperBuilder = \Mockery::mock(CarrierHelperBuilder::class)->makePartial();
        $carrierHelperBuilder->shouldReceive('getCarrierData')->andReturn($carrierData);
        $carrierHelper = $carrierHelperBuilder->getInstance();

        $this->assertEquals(CarrierHelper::UNKNOWN_CARRIER, $carrierHelper->getParentCarrierNameById(1));

        $carrierData = \Mockery::mock(CarrierData::class)->makePartial();
        $carrierData->shouldReceive('getCarriers')->andReturn([]);

        $carrierHelperBuilder = \Mockery::mock(CarrierHelperBuilder::class)->makePartial();
        $carrierHelperBuilder->shouldReceive('getCarrierData')->andReturn($carrierData);
        $carrierHelper = $carrierHelperBuilder->getInstance();
        $this->assertEquals(CarrierHelper::UNKNOWN_CARRIER, $carrierHelper->getParentCarrierNameById(1));

        $carriers = [
            [
                'id_carrier' => 5,
                'id_reference' => 1,
                'name' => 'PrestaShop',
            ],
        ];

        $carrierData = \Mockery::mock(CarrierData::class)->makePartial();
        $carrierData->shouldReceive('getCarriers')->andReturn($carriers);

        $carrierHelperBuilder = \Mockery::mock(CarrierHelperBuilder::class)->makePartial();
        $carrierHelperBuilder->shouldReceive('getCarrierData')->andReturn($carrierData);
        $carrierHelper = $carrierHelperBuilder->getInstance();
        $this->assertEquals(CarrierHelper::UNKNOWN_CARRIER, $carrierHelper->getParentCarrierNameById(1));

        $carriers = [
            [
                'id_carrier' => 1,
                'id_reference' => 2,
                'name' => 'PrestaShop',
            ],
            [
                'id_carrier' => 3,
                'id_reference' => 3,
                'name' => 'Test',
            ],
        ];

        $carrierData = \Mockery::mock(CarrierData::class)->makePartial();
        $carrierData->shouldReceive('getCarriers')->andReturn($carriers);

        $carrierHelperBuilder = \Mockery::mock(CarrierHelperBuilder::class)->makePartial();
        $carrierHelperBuilder->shouldReceive('getCarrierData')->andReturn($carrierData);
        $carrierHelper = $carrierHelperBuilder->getInstance();
        $this->assertEquals('PrestaShop', $carrierHelper->getParentCarrierNameById(1));
    }
}
