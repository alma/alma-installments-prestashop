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

namespace Alma\PrestaShop\Tests\Unit\Model;

use Alma\PrestaShop\Model\AlmaCartItemModel;
use PHPUnit\Framework\TestCase;

class AlmaCartItemModelTest extends TestCase
{
    /**
     * @var AlmaCartItemModel
     */
    protected $almaCartItemModel;

    public function setUp()
    {
        $this->almaCartItemModel = new AlmaCartItemModel($this->almaCartItemDataFactory());
    }

    /**
     * @return void
     */
    public function testGetterAlmaCartItemModel()
    {
        $this->assertInstanceOf(AlmaCartItemModel::class, $this->almaCartItemModel);
        $this->assertEquals(self::almaCartItemDataFactory()->id, $this->almaCartItemModel->getId());
        $this->assertEquals(self::almaCartItemDataFactory()->id_product_attribute, $this->almaCartItemModel->getIdProductAttribute());
        $this->assertEquals(self::almaCartItemDataFactory()->id_customization, $this->almaCartItemModel->getIdCustomization());
        $this->assertEquals(self::almaCartItemDataFactory()->quantity, $this->almaCartItemModel->getQuantity());
    }

    /**
     * @return \stdClass
     */
    public static function almaCartItemDataFactory()
    {
        $product = new \stdClass();
        $product->id = 1;
        $product->id_product_attribute = 2;
        $product->id_customization = 0;
        $product->quantity = 1;
        $product->price_without_reduction = 100.00;
        $product->reference = 'ABC123';
        $product->name = 'Name of product';

        return $product;
    }
}
