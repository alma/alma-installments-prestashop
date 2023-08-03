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
namespace Alma\PrestaShop\Tests\Unit\Lib\Model;

use Alma\PrestaShop\Helpers\ProductHelper;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Repositories\ProductRepository;
use Cart;
use PHPUnit\Framework\TestCase;
use PrestaShopDatabaseException;
use PrestaShopException;
use Product;

class CartDataTest extends TestCase
{
    /**
     * @dataProvider productsDataProvider
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function testGetCartItems($items, $expected)
    {
        $cart = $this->createMock(Cart::class);
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->method('getProductsCombinations')->willReturn($this->getCombinations());
        $productRepository->method('getProductsDetails')->willReturn($this->getVendor());

        $productHelper = $this->createMock(ProductHelper::class);
        $productHelper->method('getImageLink')->willReturn('https://prestashop-a-1-7-8-7.local.test/1-large_default/product_test.jpg');
        $productHelper->method('getProductLink')->willReturn('https://prestashop-a-1-7-8-7.local.test/1-1-product_test.html#/1-size-s/8-color-white');

        $summaryDetailsMock = ['products' => $items, 'gift_products' => []];
        $cart->method('getSummaryDetails')->willReturn($summaryDetailsMock);
        $returnItems = CartData::getCartItems($cart, $productHelper, $productRepository);
        $this->assertEquals($expected, $returnItems);
    }

    /**
     * @return array[]
     */
    public function productsDataProvider()
    {
        return [
            'Zero product in cart' => [
                'items' => [],
                'result' => [],
            ],
            'One product in cart' => [
                'items' => [
                    $this->getProduct(1, 1),
                ],
                'result' => [
                    $this->expectedProduct(1, 1),
                ],
            ],
            'Two products in cart' => [
                'items' => [
                    $this->getProduct(1, 1),
                    $this->getProduct(3, 2),
                ],
                'result' => [
                    $this->expectedProduct(1, 1),
                    $this->expectedProduct(3, 2),
                ],
            ],
            'Virtual products in cart' => [
                'items' => [
                    $this->getProduct(4, 8, false, true),
                ],
                'result' => [
                    $this->expectedProduct(4, 8),
                ],
            ],
            'Gift products in cart' => [
                'items' => [
                    $this->getProduct(2, 4, true, false),
                ],
                'result' => [
                    $this->expectedProduct(2, 4, true),
                ],
            ],
            'Virtual products in cart and Simple product' => [
                'items' => [
                    $this->getProduct(4, 8, false, true),
                    $this->getProduct(1, 1),
                ],
                'result' => [
                    $this->expectedProduct(4, 8),
                    $this->expectedProduct(1, 1),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getProduct($id, $idProductAttribute, $isGift = false, $isVirtual = false)
    {
        $product = $this->createMock(Product::class);
        $product->id = $id;
        $product->id_product = $id;
        $product->reference = 'test_' . $id;
        $product->name = 'Product ' . $id;
        $product->category = 'category_test';
        if ($isGift) {
            $product->is_gift = true;
        }
        $product->price_wt = 280.000000;
        $product->price = 280.000000;
        $product->total_wt = 280.000000;
        if ($isVirtual) {
            $product->is_virtual = $isVirtual;
        }
        $product->cart_quantity = 1;
        $product->id_product_attribute = $idProductAttribute;

        return (array) $product;
    }

    /**
     * @return array
     */
    public function expectedProduct($id, $idProductAttribute, $isGift = false)
    {
        $vendor = $this->getVendor();
        $combination = $this->getCombinations();
        $keyCombination = $id . '-' . $idProductAttribute;

        return [
            'sku' => 'test_' . $id,
            'vendor' => $vendor[$id]['manufacturer_name'],
            'title' => 'Product ' . $id,
            'variant_title' => $combination[$keyCombination],
            'quantity' => 1,
            'unit_price' => 28000,
            'line_price' => 28000,
            'is_gift' => $isGift,
            'categories' => ['category_test'],
            'url' => 'https://prestashop-a-1-7-8-7.local.test/1-1-product_test.html#/1-size-s/8-color-white',
            'picture_url' => 'https://prestashop-a-1-7-8-7.local.test/1-large_default/product_test.jpg',
            'requires_shipping' => !$vendor[$id]['is_virtual'],
            'taxes_included' => true,
        ];
    }

    /**
     * @return array[]
     */
    public function getVendor()
    {
        return [
            '1' => [
                'id_product' => 1,
                'is_virtual' => false,
                'manufacturer_name' => 'Manufacturer Test',
            ],
            '2' => [
                'id_product' => 2,
                'is_virtual' => false,
                'manufacturer_name' => 'Gift Product',
            ],
            '3' => [
                'id_product' => 3,
                'is_virtual' => false,
                'manufacturer_name' => 'Studio Design',
            ],
            '4' => [
                'id_product' => 4,
                'is_virtual' => true,
                'manufacturer_name' => 'Virtual Product',
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function getCombinations()
    {
        return [
            '1-1' => 'Color - White, Size - S',
            '2-4' => 'Color - White, Size - L',
            '3-2' => 'Color - Black, Size - S',
            '4-8' => 'Storage - 1Go',
        ];
    }
}
