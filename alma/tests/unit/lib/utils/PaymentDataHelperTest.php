<?php

/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Lib\Utils;

use PHPUnit\Framework\TestCase;
use Mockery;
use Order;

class PaymentDataHelperTest extends TestCase
{
    /**
     * test payment data
     * @dataProvider paymentDataProvider
     *
     * @return void
     */
    public function testPaymentData($data, $expected)
    {
        $dataProcessed = $data;
        $this->assertEquals($expected, $dataProcessed);
    }

    public function paymentDataProvider()
    {
        $data = [];
        $expected = [
            "website_customer_details" => [
                "new_customer" => true,
                "is_guest" => false,
                "created" => 1664790263,
                "current_order" => [
                    "purchase_amount" => 212149,
                    "payment_method" => "alma",
                    "shipping_method" => "PrestaShop",
                    "items" => [ 
                        [ 
                            "sku" => "demo_12",
                            "vendor" => "Studio Design",
                            "title" => "Mug The adventure begins",
                            "variant_title" => NULL,
                            "quantity" => 1,
                            "unit_price" => 212149,
                            "line_price" => 212149,
                            "is_gift" => false,
                            "categories" => [
                                "home-accessories" 
                            ],
                            "url" => "http://prestashop-a-1-7-8-3.local.test/home-accessories/7-mug-the-adventure-begins.html",
                            "picture_url" => "http://prestashop-a-1-7-8-3.local.test/7-large_default/mug-the-adventure-begins.jpg",
                            "requires_shipping" => true,
                            "taxes_included" => true
                        ]
                    ]
                ],
                "previous_orders" => [
                    [] 
                ],
            ],
            "payment" => [
                "installments_count" => 3,
                "deferred_days" => 0,
                "deferred_months" => 0,
                "purchase_amount" => 212149,
                "customer_cancel_url" => "http://prestashop-a-1-7-8-3.local.test/order",
                "return_url" => "http://prestashop-a-1-7-8-3.local.test/module/alma/validation",
                "ipn_callback_url" => "http://prestashop-a-1-7-8-3.local.test/module/alma/ipn",
                "shipping_address" => [
                    "line1" => "13 Boulevard de Picpus",
                    "postal_code" => "75012",
                    "city" => "Paris",
                    "country" => "FR",
                    "county_sublocality" => NULL,
                    "state_province" => NULL,
                ],
                "shipping_info" => [
                    "selected_options" => [
                        "amount" => 0,
                        "carrier" => "PrestaShop",
                        "title" => "Pick up in-store",
                        "express_delivery" => NULL,
                        "pickup_delivery" => NULL
                    ],
                    "available_options" => [
                        [
                            "amount" => 0,
                            "carrier" => "PrestaShop",
                            "title" => "Pick up in-store",
                            "express_delivery" => NULL,
                            "pickup_delivery" => NULL,
                        ],
                        [
                            "amount" => 700,
                            "carrier" => "My carrier",
                            "title" => "Delivery next day!",
                            "express_delivery" => NULL,
                            "pickup_delivery" => NULL,
                        ] 
                    ]
                ],
                "cart" => [
                    "items" => [
                        [
                            "sku" => "demo_12",
                            "vendor" => "Studio Design",
                            "title" => "Mug The adventure begins",
                            "variant_title" => NULL,
                            "quantity" => 1,
                            "unit_price" => 212149,
                            "line_price" => 212149,
                            "is_gift" => false,
                            "categories" => [
                                "home-accessories"
                            ],
                            "url" => "http://prestashop-a-1-7-8-3.local.test/home-accessories/7-mug-the-adventure-begins.html",
                            "picture_url" => "http://prestashop-a-1-7-8-3.local.test/7-large_default/mug-the-adventure-begins.jpg",
                            "requires_shipping" =>  true,
                            "taxes_included" => true 
                        ]
                    ],
                    "discounts" => [],
                ],
                "billing_address" => [
                    "line1" => "13 Boulevard de Picpus",
                    "postal_code" => "75012",
                    "city" => "Paris",
                    "country" => "FR",
                    "county_sublocality" => NULL,
                    "state_province" => NULL
                ],
                "custom_data" => [
                    "cart_id" => 36,
                    "purchase_amount_new_conversion_func" => 212149,
                    "cart_totals" => 2121.49,
                    "cart_totals_high_precision" => "2,121.4899999999997817"
                ],
                "locale" => "en-US"
            ],
            "customer" => [
                "first_name" => "Benjamin",
                "last_name" => "Freoua",
                "email" => "benjamin.freoua@getalma.eu",
                "birth_date" => NULL,
                "addresses" => [
                    [
                        "line1" => "13 Boulevard de Picpus",
                        "postal_code" => "75012",
                        "city" => "Paris",
                        "country" => "FR",
                        "county_sublocality" => NULL,
                        "state_province" => NULL
                    ]
                ],
                "phone" => "0613595410",
                "country" => "FR",
                "county_sublocality" => NULL,
                "state_province" => NULL
            ]
        ];

        return [
            'payment data' => [
                $data,
                $expected
            ]
        ];
    }
}
