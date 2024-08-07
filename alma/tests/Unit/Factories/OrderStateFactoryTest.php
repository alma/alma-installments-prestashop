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

use Alma\PrestaShop\Factories\OrderStateFactory;
use PHPUnit\Framework\TestCase;

class OrderStateFactoryTest extends TestCase
{
    /**
     * @var OrderStateFactoryTest
     */
    protected $orderStateFactory;

    public function setUp()
    {
        $this->orderStateFactory = new OrderStateFactory();
    }

    public function testGetOrderState()
    {
        $expected = [
            ['id_order_state' => '17', 'invoice' => '0', 'send_email' => '0', 'module_name' => 'ps_checkout', 'color' => '#3498D8', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Authorized. To be captured by merchant', 'template' => ''],
            ['id_order_state' => '10', 'invoice' => '0', 'send_email' => '1', 'module_name' => 'ps_wirepayment', 'color' => '#34209E', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Awaiting bank wire payment', 'template' => 'bankwire'],
            ['id_order_state' => '13', 'invoice' => '0', 'send_email' => '0', 'module_name' => 'ps_cashondelivery', 'color' => '#34209E', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Awaiting Cash On Delivery validation', 'template' => 'cashondelivery'],
            ['id_order_state' => '1', 'invoice' => '0', 'send_email' => '1', 'module_name' => 'ps_checkpayment', 'color' => '#34209E', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Awaiting check payment', 'template' => 'cheque'],
            ['id_order_state' => '6', 'invoice' => '0', 'send_email' => '1', 'module_name' => '', 'color' => '#2C3E50', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Canceled', 'template' => 'order_canceled'],
            ['id_order_state' => '5', 'invoice' => '1', 'send_email' => '0', 'module_name' => '', 'color' => '#01B887', 'unremovable' => '1', 'hidden' => '0', 'logable' => '1', 'delivery' => '1', 'shipped' => '1', 'paid' => '1', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Delivered', 'template' => ''],
            ['id_order_state' => '12', 'invoice' => '0', 'send_email' => '1', 'module_name' => '', 'color' => '#34209E', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'On backorder (not paid)', 'template' => 'outofstock'],
            ['id_order_state' => '9', 'invoice' => '1', 'send_email' => '1', 'module_name' => '', 'color' => '#3498D8', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '1', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'On backorder (paid)', 'template' => 'outofstock'],
            ['id_order_state' => '16', 'invoice' => '0', 'send_email' => '0', 'module_name' => 'ps_checkout', 'color' => '#3498D8', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Partial payment', 'template' => ''],
            ['id_order_state' => '15', 'invoice' => '0', 'send_email' => '0', 'module_name' => 'ps_checkout', 'color' => '#01B887', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Partial refund', 'template' => ''],
            ['id_order_state' => '2', 'invoice' => '1', 'send_email' => '1', 'module_name' => '', 'color' => '#3498D8', 'unremovable' => '1', 'hidden' => '0', 'logable' => '1', 'delivery' => '0', 'shipped' => '0', 'paid' => '1', 'pdf_invoice' => '1', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Payment accepted', 'template' => 'payment'],
            ['id_order_state' => '8', 'invoice' => '0', 'send_email' => '1', 'module_name' => '', 'color' => '#E74C3C', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Payment error', 'template' => 'payment_error'],
            ['id_order_state' => '3', 'invoice' => '1', 'send_email' => '1', 'module_name' => '', 'color' => '#3498D8', 'unremovable' => '1', 'hidden' => '0', 'logable' => '1', 'delivery' => '1', 'shipped' => '0', 'paid' => '1', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Processing in progress', 'template' => 'preparation'],
            ['id_order_state' => '7', 'invoice' => '1', 'send_email' => '1', 'module_name' => '', 'color' => '#01B887', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Refunded', 'template' => 'refund'],
            ['id_order_state' => '11', 'invoice' => '1', 'send_email' => '1', 'module_name' => '', 'color' => '#3498D8', 'unremovable' => '1', 'hidden' => '0', 'logable' => '1', 'delivery' => '0', 'shipped' => '0', 'paid' => '1', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Remote payment accepted', 'template' => 'payment'],
            ['id_order_state' => '4', 'invoice' => '1', 'send_email' => '1', 'module_name' => '', 'color' => '#01B887', 'unremovable' => '1', 'hidden' => '0', 'logable' => '1', 'delivery' => '1', 'shipped' => '1', 'paid' => '1', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Shipped', 'template' => 'shipped'],
            ['id_order_state' => '14', 'invoice' => '0', 'send_email' => '0', 'module_name' => 'ps_checkout', 'color' => '#34209E', 'unremovable' => '1', 'hidden' => '0', 'logable' => '0', 'delivery' => '0', 'shipped' => '0', 'paid' => '0', 'pdf_invoice' => '0', 'pdf_delivery' => '0', 'deleted' => '0', 'id_lang' => '1', 'name' => 'Waiting for payment', 'template' => ''],
        ];

        $this->assertEquals($expected, $this->orderStateFactory->getOrderStates(1));
    }
}
