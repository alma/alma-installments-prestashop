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

use Alma\PrestaShop\Helpers\ProductHelper;
use PHPUnit\Framework\TestCase;

class ProductHelperTest extends TestCase
{
    /**
     * @var ProductHelper
     */
    protected $productHelper;
    /**
     * @var \Category
     */
    protected $categoryMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Product|(\Product&\PHPUnit_Framework_MockObject_MockObject)
     */
    protected $productMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->productHelper = new ProductHelper();
        $this->categoryMock = $this->createMock(\Category::class);
        $this->productMock = $this->createMock(\Product::class);
    }

    public function tearDown()
    {
        $this->productHelper = null;
        $this->categoryMock = null;
        $this->productMock = null;
    }

    /**
     * @return void
     */
    public function testGetCategoryNameWithArray()
    {
        $productArray = [
            'id_product' => 1,
            'category' => 'category-name',
        ];
        $expected = ['category-name'];

        $this->assertEquals($expected, $this->productHelper->getCategoriesName($productArray));
    }

    /**
     * @return void
     */
    public function testGetCategoryNameWithCategoryObject()
    {
        $this->categoryMock->id = 23;
        $this->categoryMock->id_category = '23';
        $this->categoryMock->name = [
            'Category Name',
            'Category Name 2',
        ];
        $product = [
            'category' => $this->categoryMock,
        ];

        $expected = [
            'Category Name',
            'Category Name 2',
        ];

        $this->assertEquals($expected, $this->productHelper->getCategoriesName($product));
    }

    /**
     * @return void
     */
    public function testGetCategoryNameWithObjectProduct()
    {
        $this->productMock->id = 25;
        $this->productMock->category = 'category-name-object';

        $this->assertEquals(['category-name-object'], $this->productHelper->getCategoriesName($this->productMock));
    }

    /**
     * @dataProvider wrongDataProvider
     *
     * @return void
     */
    public function testGetCategoryNameWithWrongData($product)
    {
        $this->assertEquals([], $this->productHelper->getCategoriesName($product));
    }

    /**
     * @return array
     */
    public function wrongDataProvider()
    {
        $object = new \stdClass();
        $object->id = 24;
        $object->name = 'category-name';
        $product = [
            'category' => $object,
        ];

        return [
            'object not Category' => [
                $product,
            ],
            'product is string' => [
                'product',
            ],
            'product is int' => [1],
            'product is null' => [null],
            'product is false' => [false],
            'product is true' => [true],
        ];
    }
}
