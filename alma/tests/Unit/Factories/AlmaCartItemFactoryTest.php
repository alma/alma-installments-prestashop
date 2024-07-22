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

namespace Alma\PrestaShop\Tests\Unit\Factories;

use Alma\PrestaShop\Exceptions\AlmaCartItemFactoryException;
use Alma\PrestaShop\Factories\AlmaCartItemFactory;
use Alma\PrestaShop\Model\AlmaCartItemModel;
use Alma\PrestaShop\Tests\Unit\Model\AlmaCartItemModelTest;
use PHPUnit\Framework\TestCase;

class AlmaCartItemFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->almaCartITemModelDataFactory = AlmaCartItemModelTest::almaCartItemDataFactory();
        $this->almaCartItemFactory = new AlmaCartItemFactory();
        $this->almaCartItemModel = new AlmaCartItemModel($this->almaCartITemModelDataFactory);
    }

    /**
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactory()
    {
        $this->assertInstanceOf(AlmaCartItemModel::class, $this->almaCartItemFactory->create($this->almaCartITemModelDataFactory));
    }

    /**
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactoryWithObject()
    {
        $this->assertEquals($this->almaCartItemModel, $this->almaCartItemFactory->create($this->almaCartITemModelDataFactory));
    }

    /**
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactoryWithArray()
    {
        $product = [
            'id' => 1,
            'id_product_attribute' => 2,
            'id_customization' => 0,
            'quantity' => 1,
            'price_without_reduction' => 100.00,
            'reference' => 'ABC123',
            'name' => 'Name of product',
        ];

        $this->assertEquals($this->almaCartItemModel, $this->almaCartItemFactory->create($product));
    }

    /**
     * @dataProvider wrongProductDataProvider
     *
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactoryWithStringOrInt($product)
    {
        $this->expectException(AlmaCartItemFactoryException::class);
        $this->almaCartItemFactory->create($product);
    }

    /**
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactoryWithWrongDataInArray()
    {
        $product = [
            'wrongkeyid' => 1,
            'id_product_attribute' => 2,
            'id_customization' => 0,
            'quantity' => 1,
        ];

        $this->expectException(AlmaCartItemFactoryException::class);
        $this->almaCartItemFactory->create($product);
    }

    /**
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactoryWithWrongDataInObject()
    {
        $product = new \stdClass();
        $product->wrongkeyid = 1;
        $product->id_product_attribute = 2;
        $product->id_customization = 0;
        $product->quantity = 1;

        $this->expectException(AlmaCartItemFactoryException::class);
        $this->almaCartItemFactory->create($product);
    }

    /**
     * @throws AlmaCartItemFactoryException
     */
    public function testCreateAlmaCartItemFactoryAndConvertIdProductToId()
    {
        $product = new \stdClass();
        $product->id_product = 1;
        $product->id_product_attribute = 2;
        $product->id_customization = 0;
        $product->quantity = 1;
        $product->price_without_reduction = 100.00;
        $product->reference = 'ABC123';
        $product->name = 'Name of product';

        $this->assertEquals($this->almaCartItemModel, $this->almaCartItemFactory->create($product));
    }

    /**
     * @return array
     */
    public function wrongProductDataProvider()
    {
        return [
            'String' => [
                'toto',
            ],
            'Int' => [
                1,
            ],
            'Empty' => [
                [],
            ],
            'Boolean' => [
                true,
            ],
        ];
    }
}
