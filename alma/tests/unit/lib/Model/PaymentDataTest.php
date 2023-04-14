<?php
/**
 * 2018-2023 Alma SAS
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
use Context;
use Customer;
use Language;
use PHPUnit\Framework\TestCase;
use Cart;
use Product;

class PaymentDataTest extends TestCase
{
    public function testdataFromCart()
    {
        $expectedDataPayment = [];
        $product = $this->createMock(Product::class);
        $product->id = 1;
        $cart = $this->createMock(Cart::class);
        $cart->id_customer = 1;
        $cart->id_address_delivery = 1;
        $cart->id_address_invoice = 1;
        //$cart->setProductCustomizedDatas();
        var_dump($cart);
        $context = $this->createMock(Context::class);
        $language = $this->createMock(Language::class);
        $language->iso_code = 'fr';
        $context->language = $language;
        $customer = $this->createMock(Customer::class);
        $customer->firstname = 'Benjamin';
        $context->customer = $customer;
        $address = $this->createMock(Address::class);
        $address->id = 1;
        $address->id_customer = 1;
        $address->address1 = "1 rue de Rivoli";
        $feePlans = [];
        $returnData = PaymentData::dataFromCart($cart, $context, $feePlans,true);

        $this->assertEquals($expectedDataPayment, $returnData);
    }
}