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

use Alma\PrestaShop\Builders\MediaHelperBuilder;
use Alma\PrestaShop\Factories\PhpFactory;
use Alma\PrestaShop\Helpers\MediaHelper;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class MediaHelperTest extends TestCase
{
    public function testGetLogoName()
    {
        $mediaHelperBuilder = new MediaHelperBuilder();
        $mediaHelper = $mediaHelperBuilder->getInstance();
        $this->assertEquals('p3x_logo.svg' , $mediaHelper->getLogoName(3 , false) );

        $mediaHelperBuilder = new MediaHelperBuilder();
        $mediaHelper = $mediaHelperBuilder->getInstance();
        $this->assertEquals('15j_logo.svg' , $mediaHelper->getLogoName(15 , true) );
    }

    public function testGetIconPathAlmaTiny()
    {
        $phpFactory = \Mockery::mock(PHPFactory::class)->makePartial();
        $phpFactory->shouldReceive('is_callable')->andReturn('false');
        $mediaHelperBuilder = \Mockery::mock(MediaHelperBuilder::class)->makePartial();
        $mediaHelperBuilder->shouldReceive('getPhpFactory')->andReturn($phpFactory);
        $mediaHelper = $mediaHelperBuilder->getInstance();

        $this->assertEquals('/modules/alma/views/img/logos/alma_tiny.svg', $mediaHelper->getIconPathAlmaTiny());

        $phpFactory = \Mockery::mock(PHPFactory::class)->makePartial();
        $phpFactory->shouldReceive('is_callable')->andReturn('true');
        $mediaHelperBuilder = \Mockery::mock(MediaHelperBuilder::class)->makePartial();
        $mediaHelperBuilder->shouldReceive('getPhpFactory')->andReturn($phpFactory);
        $mediaHelper = $mediaHelperBuilder->getInstance();

        $this->assertEquals('/modules/alma/views/img/logos/alma_tiny.svg', $mediaHelper->getIconPathAlmaTiny());
    }
}
