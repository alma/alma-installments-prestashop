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

use Alma\PrestaShop\Exceptions\ProductException;
use Alma\PrestaShop\Factories\ProductFactory;
use Alma\PrestaShop\Helpers\InsuranceHelper;
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
     * @var InsuranceHelper
     */
    protected $insuranceHelperMock;
    /**
     * @var ProductFactory
     */
    protected $productFactoryMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->categoryMock = $this->createMock(\Category::class);
        $this->productMock = $this->getMockBuilder(\Product::class)
            ->setMethods(['getAttributeCombination'])
            ->getMock();
        $this->insuranceHelperMock = $this->createMock(InsuranceHelper::class);
        $this->productFactoryMock = $this->createMock(ProductFactory::class);
        $this->productHelper = new ProductHelper(
            $this->insuranceHelperMock,
            $this->productFactoryMock
        );
    }

    public function tearDown()
    {
        $this->productHelper = null;
        $this->categoryMock = null;
        $this->productMock = null;
        $this->insuranceHelperMock = null;
        $this->productFactoryMock = null;
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

    /**
     * @dataProvider wrongParamsForGetAttributeCombinationDataProvider
     *
     * @return void
     *
     * @throws ProductException
     */
    public function testGetAttributeCombinationsByProductIdWithWrongParams($productId, $languageId)
    {
        $this->expectException(ProductException::class);
        $this->productHelper->getAttributeCombinationsByProductId($productId, $languageId);
    }

    public function testGetAttributeCombinationsByProductIdWithRightParams()
    {
        $this->productMock->id = 2;
        $attributeCombinationsReturned = [
            [
                'id_product_attribute' => '9',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 1200,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => '1',
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '1',
            ],
            [
                'id_product_attribute' => '10',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 300,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => null,
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '2',
            ],
            [
                'id_product_attribute' => '11',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 300,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => null,
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '3',
            ],
            [
                'id_product_attribute' => '12',
                'id_product' => (string) $this->productMock->id,
                'reference' => 'demo_3',
                'supplier_reference' => '',
                'location' => '',
                'ean13' => '',
                'isbn' => '',
                'upc' => '',
                'mpn' => '',
                'wholesale_price' => '0.000000',
                'price' => '0.000000',
                'ecotax' => '0.000000',
                'quantity' => 300,
                'weight' => '0.000000',
                'unit_price_impact' => '0.000000',
                'default_on' => null,
                'minimal_quantity' => '1',
                'low_stock_threshold' => null,
                'low_stock_alert' => '0',
                'available_date' => '0000-00-00',
                'id_shop' => '1',
                'id_attribute_group' => '1',
                'is_color_group' => '0',
                'group_name' => null,
                'attribute_name' => null,
                'id_attribute' => '4',
            ],
        ];
        $languageId = 4;

        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->productMock->id)
            ->willReturn($this->productMock);

        $this->assertEquals(
            $attributeCombinationsReturned,
            $this->productHelper->getAttributeCombinationsByProductId($this->productMock->id, $languageId)
        );
    }

    /**
     * @return array
     */
    public function wrongParamsForGetAttributeCombinationDataProvider()
    {
        return [
            'params are null' => [
                'productId' => null,
                'languageId' => null,
            ],
            'params are string' => [
                'productId' => 'wrong productId',
                'languageId' => 'wrongLanguageId',
            ],
            'productId is int and languageId is null' => [
                'productId' => 2,
                'languageId' => null,
            ],
            'productId is null and languageId is int' => [
                'productId' => null,
                'languageId' => 3,
            ],
        ];
    }
}
