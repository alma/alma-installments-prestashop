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

use Address;
use Alma\PrestaShop\Model\PaymentData;
use Cart;
use Context;
use Customer;
use Exception;
use Language;
use PHPUnit\Framework\TestCase;
use Product;

class PaymentDataTest extends TestCase
{
    /**
     * @return void
     *
     * @throws Exception
     */
    public function testdataFromCart()
    {
        $this->markTestSkipped(
            'Need refacto'
        );
        $expectedDataPayment = [];
        $address = $this->createMock(Address::class);
        $address->id = 1;
        $address->id_customer = 1;
        $address->address1 = '1 rue de Rivoli';
        $product = $this->createMock(Product::class);
        $product->id = 1;
        $product->name = 'Product Test';
        $summaryDetailsMock = ['products' => [], 'gift_products' => []];
        $cart = $this->createMock(Cart::class);
        $cart->method('getSummaryDetails')->willReturn($summaryDetailsMock);
        $cart->id_customer = 1;
        $cart->id_address_delivery = 1;
        $cart->id_address_invoice = 1;
        $context = $this->createMock(Context::class);
        $language = $this->createMock(Language::class);
        $language->iso_code = 'fr';
        $context->language = $language;
        $customer = $this->createMock(Customer::class);
        $customer->firstname = 'Benjamin';
        $customer->id = 1;
        $context->customer = $customer;
        $feePlans = [];
        $returnData = PaymentData::dataFromCart($cart, $context, $feePlans, true);

        $this->assertEquals($expectedDataPayment, $returnData);
    }
}
