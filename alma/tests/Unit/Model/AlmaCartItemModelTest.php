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
    /**
     * @var array
     */
    protected $almaCartItemArrayData;

    public function setUp()
    {
        $this->almaCartItemArrayData = self::almaCartItemArrayData();
        $this->almaCartItemModel = new AlmaCartItemModel($this->almaCartItemArrayData);
    }

    public function tearDown()
    {
        $this->almaCartItemModel = null;
    }

    /**
     * @return void
     */
    public function testGetterAlmaCartItemModel()
    {
        $this->assertInstanceOf(AlmaCartItemModel::class, $this->almaCartItemModel);
        $this->assertEquals($this->almaCartItemArrayData['id'], $this->almaCartItemModel->getId());
        $this->assertEquals($this->almaCartItemArrayData['id_product_attribute'], $this->almaCartItemModel->getIdProductAttribute());
        $this->assertEquals($this->almaCartItemArrayData['id_customization'], $this->almaCartItemModel->getIdCustomization());
        $this->assertEquals($this->almaCartItemArrayData['quantity'], $this->almaCartItemModel->getQuantity());
        $this->assertEquals($this->almaCartItemArrayData['price_without_reduction'], $this->almaCartItemModel->getPriceWithoutReduction());
        $this->assertEquals($this->almaCartItemArrayData['reference'], $this->almaCartItemModel->getReference());
        $this->assertEquals($this->almaCartItemArrayData['name'], $this->almaCartItemModel->getName());
    }

    public function testIdCustomizationNullSaveZero()
    {
        $this->almaCartItemArrayData['id_customization'] = null;
        $almaCartItem = new AlmaCartItemModel($this->almaCartItemArrayData);
        $this->assertEquals(0, $almaCartItem->getIdCustomization());
    }

    /**
     * @return array
     */
    public static function almaCartItemArrayData()
    {
        return [
            'id' => '1',
            'id_product_attribute' => '2',
            'id_customization' => 0,
            'quantity' => 3,
            'price_without_reduction' => 100.00,
            'reference' => 'ABC123',
            'name' => 'Name of product',
        ];
    }
}
