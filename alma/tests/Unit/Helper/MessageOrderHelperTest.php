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

use Alma\API\Entities\Insurance\Contract;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Exceptions\MessageOrderException;
use Alma\PrestaShop\Helpers\MessageOrderHelper;
use Alma\PrestaShop\Services\InsuranceApiService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageOrderHelperTest extends TestCase
{
    /**
     * @var MessageOrderHelper
     */
    protected $messageOrderHelper;
    protected $insuranceApiService;
    protected $context;
    protected $module;
    /**
     * @var (Contract&MockObject)|MockObject
     */
    protected $insuranceContract;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->module = $this->createMock(\Module::class);
        $this->context = $this->createMock(\Context::class);
        $this->insuranceApiService = $this->createMock(InsuranceApiService::class);

        $priceHelperBuilder = new PriceHelperBuilder();
        $priceHelper = $priceHelperBuilder->getInstance();

        $this->messageOrderHelper = new MessageOrderHelper(
            $this->module,
            $this->context,
            $this->insuranceApiService,
            $priceHelper
        );
        $this->createMock(\Language::class);
        $this->createMock(\Shop::class);
        $this->context->language = $this->createMock(\Language::class);
        $this->product = $this->createMock(\Product::class);
        $this->insuranceContract = \Mockery::mock(Contract::class);
    }

    /**
     * Given right data, the method should return a string text of message
     *
     * @return void
     *
     * @throws MessageOrderException
     */
    public function testGetMessageForRefundWithRightData()
    {
        $almaInsuranceProduct = [
            'id_alma_insurance_product' => 32,
            'id_cart' => 36,
            'id_product' => 1,
            'id_shop' => 1,
            'id_product_attribute' => 1,
            'id_customization' => 0,
            'id_product_insurance' => 20,
            'id_product_attribute_insurance' => 40,
            'id_address_delivery' => 7,
            'id_order' => 36,
            'price' => 12938,
            'insurance_contract_id' => 'insurance_contract_4D6UBXtagTd5DZlTGPpKuT',
            'cms_reference' => '1-1',
            'product_price' => 35000,
            'date_add' => '2024-03-01 11:16:39',
            'subscription_id' => 'subscription_51Jr3LqiVTQpe4a0rWBjfV',
            'subscription_amount' => 12938,
            'subscription_broker_id' => '0e1d4e1b-2b12-4ad7-9212-0fb6d66f2cb4',
            'subscription_broker_reference' => 'TGLC47CQ',
            'subscription_state' => 'started',
            'date_of_cancelation' => '0000-00-00 00:00:00',
            'reason_of_cancelation' => '',
            'is_refunded' => 0,
            'date_of_refund' => '0000-00-00 00:00:00',
            'date_of_cancelation_request' => '0000-00-00 00:00:00',
            'mode' => 'test',
        ];
        $this->context->language->id = 1;
        $this->product->name = 'Hummingbird printed t-shirt';

        $this->insuranceContract->shouldReceive('getName')
            ->andReturn('Insurance Contract');

        $expected = 'The Insurance Insurance Contract at 129.38€ for the product Hummingbird printed t-shirt has been cancelled.
        Please refund the customer.
        Action Required: Refund the customer for the affected subscriptions.
        Thank you.';

        $this->module->expects($this->once())
            ->method('l')
            ->with('The Insurance Insurance Contract at 129.38€ for the product Hummingbird printed t-shirt has been cancelled.
            Please refund the customer.
            Action Required: Refund the customer for the affected subscriptions.
            Thank you.', 'messageOrderService')
            ->willReturn('The Insurance Insurance Contract at 129.38€ for the product Hummingbird printed t-shirt has been cancelled.
        Please refund the customer.
        Action Required: Refund the customer for the affected subscriptions.
        Thank you.');
        $this->insuranceApiService->expects($this->once())
            ->method('getInsuranceContract')->with(
                $almaInsuranceProduct['insurance_contract_id'],
                $almaInsuranceProduct['cms_reference'],
                $almaInsuranceProduct['price']
            )->willReturn($this->insuranceContract);
        $this->assertEquals($expected, $this->messageOrderHelper->getInsuranceCancelMessageRefundAllow($almaInsuranceProduct));
    }

    /**
     * Given wrong data, the method should throw an exception
     *
     * @return void
     *
     * @throws MessageOrderException
     */
    public function testGetMessageForRefundWithWrongData()
    {
        $this->expectException(MessageOrderException::class);
        $this->messageOrderHelper->getInsuranceCancelMessageRefundAllow('string');
    }
}
