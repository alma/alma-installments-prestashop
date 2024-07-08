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

namespace Alma\PrestaShop\Tests\Unit\Builders\Helpers;

use Alma\PrestaShop\Builders\Models\MediaHelperBuilder;
use Alma\PrestaShop\Factories\MediaFactory;
use Alma\PrestaShop\Factories\PhpFactory;
use Alma\PrestaShop\Helpers\MediaHelper;
use PHPUnit\Framework\TestCase;

class MediaHelperBuilderTest extends TestCase
{
    /**
     * @var MediaHelperBuilderTest
     */
    protected $mediaHelperBuilder;

    public function setUp()
    {
        $this->mediaHelperBuilder = new MediaHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(MediaHelper::class, $this->mediaHelperBuilder->getInstance());
    }

    public function testGetMediaFactory()
    {
        $this->assertInstanceOf(MediaFactory::class, $this->mediaHelperBuilder->getMediaFactory());
        $this->assertInstanceOf(MediaFactory::class, $this->mediaHelperBuilder->getMediaFactory(
            \Mockery::mock(MediaFactory::class)
        ));
    }

    public function testGetPhpFactory()
    {
        $this->assertInstanceOf(PhpFactory::class, $this->mediaHelperBuilder->getPhpFactory());
        $this->assertInstanceOf(PhpFactory::class, $this->mediaHelperBuilder->getPhpFactory(
            new PhpFactory()
        ));
    }
}
