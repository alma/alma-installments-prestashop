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

namespace Alma\PrestaShop\Tests\Unit\Model;

use Alma\PrestaShop\Builders\Models\PaymentDataBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Helpers\SettingsHelper;
use PHPUnit\Framework\TestCase;

class PaymentDataTest extends TestCase
{
    /**
     * @var \Customer
     */
    protected $customer;
    protected $shippingAddress;
    protected $billingAddress;
    protected $paymentData;
    protected $cartMock;
    protected $linkMock;
    protected $settingsHelperMock;
    protected $contextMock;

    public function setUp()
    {
        $paymentDataBuilderMock = \Mockery::mock(PaymentDataBuilder::class)->makePartial();
        $contextFactoryMock = $this->createMock(ContextFactory::class);
        $this->contextMock = $this->createMock(\Context::class);

        $this->cartMock = $this->createMock(\Cart::class);
        $this->cartMock->id = '42';
        $this->cartMock->method('getSummaryDetails')->willReturn(['products' => [], 'gift_products' => []]);
        $this->cartMock->method('getCartRules')->willReturn([]);
        $this->contextMock->cart = $this->cartMock;

        $this->linkMock = $this->createMock(\Link::class);
        $this->linkMock->method('getPageLink')->willReturn('');
        $this->linkMock->method('getModuleLink')->willReturn('');
        $this->contextMock->link = $this->linkMock;

        $this->contextMock->language = $this->createMock(\Language::class);

        $this->settingsHelperMock = $this->createMock(SettingsHelper::class);

        $contextFactoryMock->method('getContext')->willReturn($this->contextMock);
        $paymentDataBuilderMock->shouldReceive('getContextFactory')->andReturn($contextFactoryMock);
        $paymentDataBuilderMock->shouldReceive('getSettingsHelper')->andReturn($this->settingsHelperMock);
        $this->paymentData = $paymentDataBuilderMock->getInstance();

        $this->customer = $this->createMock(\Customer::class);
        $this->shippingAddress = $this->createMock(\Address::class);
        $this->billingAddress = $this->createMock(\Address::class);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    private $dataPaymentExpected = [
        'website_customer_details' => [
            'new_customer' => true,
            'is_guest' => false,
            'created' => false,
            'current_order' => [
                'purchase_amount' => 10000,
                'created' => false,
                'payment_method' => 'alma',
                'shipping_method' => 'Unknown',
                'items' => [],
            ],
            'previous_orders' => [
                0 => [],
            ],
        ],
        'payment' => [
            'installments_count' => 3,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'purchase_amount' => 10000,
            'customer_cancel_url' => '',
            'return_url' => '',
            'ipn_callback_url' => '',
            'shipping_address' => [
                'line1' => null,
                'postal_code' => null,
                'city' => null,
                'country' => 'FR',
                'county_sublocality' => null,
                'state_province' => '',
            ],
            'shipping_info' => null,
            'billing_address' => [
                'line1' => null,
                'postal_code' => null,
                'city' => null,
                'country' => 'FR',
                'county_sublocality' => null,
                'state_province' => '',
            ],
            'custom_data' => [
                'cart_id' => '42',
                'purchase_amount_new_conversion_func' => 10000,
                'cart_totals' => 100,
                'cart_totals_high_precision' => '100.0000000000000000',
                'poc' => [
                    0 => 'data-for-risk',
                ],
            ],
            'locale' => 'fr-FR',
            'cart' => [
                'items' => [],
                'discounts' => [],
            ],
        ],
        'customer' => [
            'state_province' => 'test',
        ],
    ];

    private $purchaseAmount = 100;
    private $feePlans = [
        'installmentsCount' => 3,
        'deferredDays' => 0,
        'deferredMonths' => 0,
        'deferred_trigger_limit_days' => 0,
    ];
    private $countryShippingAddress = 'FR';
    private $countryBillingAddress = 'FR';
    private $locale = 'fr-FR';
    private $customerData = [
        'state_province' => 'test',
    ];

    public function testBuildDataPaymentForPnx()
    {
        $this->assertEquals($this->dataPaymentExpected, $this->paymentData->buildDataPayment(
            $this->customer,
            $this->purchaseAmount,
            $this->feePlans,
            $this->shippingAddress,
            $this->countryShippingAddress,
            $this->locale,
            $this->billingAddress,
            $this->countryBillingAddress,
            $this->customerData));
    }

    public function testBuildDataPaymentWithDeferredTrigger()
    {
        $this->settingsHelperMock->method('isDeferredTriggerLimitDays')->willReturn(true);

        $this->dataPaymentExpected['payment']['deferred'] = 'trigger';
        $this->dataPaymentExpected['payment']['deferred_description'] = 'At shipping';

        $this->assertEquals($this->dataPaymentExpected, $this->paymentData->buildDataPayment(
            $this->customer,
            $this->purchaseAmount,
            $this->feePlans,
            $this->shippingAddress,
            $this->countryShippingAddress,
            $this->locale,
            $this->billingAddress,
            $this->countryBillingAddress,
            $this->customerData));
    }

    public function testBuildDataPaymentWithInPage()
    {
        $this->settingsHelperMock->method('isInPageEnabled')->willReturn(true);
        $this->dataPaymentExpected['payment']['origin'] = 'online_in_page';

        $this->assertEquals($this->dataPaymentExpected, $this->paymentData->buildDataPayment(
            $this->customer,
            $this->purchaseAmount,
            $this->feePlans,
            $this->shippingAddress,
            $this->countryShippingAddress,
            $this->locale,
            $this->billingAddress,
            $this->countryBillingAddress,
            $this->customerData));
    }
}
