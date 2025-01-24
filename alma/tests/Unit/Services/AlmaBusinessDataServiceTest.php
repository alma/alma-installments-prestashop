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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\DTO\MerchantBusinessEvent\OrderConfirmedBusinessEvent;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\AlmaBusinessDataModel;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Repositories\AlmaBusinessDataRepository;
use Alma\PrestaShop\Services\AlmaBusinessDataService;
use PHPUnit\Framework\TestCase;

class AlmaBusinessDataServiceTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Services\AlmaBusinessDataService
     */
    protected $almaBusinessDataService;
    /**
     * @var \Alma\PrestaShop\Model\AlmaBusinessDataModel
     */
    protected $almabusinessDataModelMock;
    /**
     * @var \Alma\PrestaShop\Logger
     */
    protected $loggerMock;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    protected $clientModelMock;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    protected $settingsHelperMock;
    /**
     * @var \Alma\PrestaShop\Helpers\ConfigurationHelper
     */
    protected $configurationHelperMock;
    /**
     * @var \Alma\PrestaShop\Repositories\AlmaBusinessDataRepository|(\Alma\PrestaShop\Repositories\AlmaBusinessDataRepository&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $almaBusinessDataRepositoryMock;
    /**
     * @var \Alma\API\Entities\DTO\MerchantBusinessEvent\OrderConfirmedBusinessEvent|(\Alma\API\Entities\DTO\MerchantBusinessEvent\OrderConfirmedBusinessEvent&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfirmedBusinessEventMock;

    public function setUp()
    {
        $this->clientModelMock = $this->createMock(ClientModel::class);
        $this->loggerMock = $this->createMock(Logger::class);
        $this->almabusinessDataModelMock = $this->createMock(AlmaBusinessDataModel::class);
        $this->almaBusinessDataRepositoryMock = $this->createMock(AlmaBusinessDataRepository::class);
        $this->almaBusinessDataService = new AlmaBusinessDataService(
            $this->clientModelMock,
            $this->loggerMock,
            $this->almabusinessDataModelMock,
            $this->almaBusinessDataRepositoryMock
        );
    }

    /**
     * @return void
     */
    public function testRunOrderConfirmedBusinessEvent()
    {
        $orderId = '2';
        $cartId = '3';
        $almaBusinessData = [
            'id_alma_business_data' => '5',
            'id_cart' => '3',
            'id_order' => '2',
            'alma_payment_id' => 'alma_payment_id',
            'is_bnpl_eligible' => '1',
            'plan_key' => 'general_2_0_0',
        ];
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('id_order', $orderId, 'id_cart = ' . $cartId);
        $this->almabusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with($cartId)
            ->willReturn($almaBusinessData);
        $this->clientModelMock->expects($this->once())
            ->method('sendOrderConfirmedBusinessEvent')
            ->with($this->isInstanceOf(OrderConfirmedBusinessEvent::class));
        $this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId);
    }

    /**
     * @return void
     */
    public function testRunOrderConfirmedBusinessEventWithWrongParamLoggerErrorNoThrow()
    {
        $orderId = '2';
        $cartId = '3';
        $almaBusinessData = [
            'id_alma_business_data' => '5',
            'id_cart' => '3',
            'id_order' => '2',
            'alma_payment_id' => 'alma_payment_id',
            'is_bnpl_eligible' => '1',
            'plan_key' => '',
        ];
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('id_order', $orderId, 'id_cart = ' . $cartId);
        $this->almabusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with($cartId)
            ->willReturn($almaBusinessData);
        $this->clientModelMock->expects($this->never())
            ->method('sendOrderConfirmedBusinessEvent');
        $this->loggerMock->expects($this->once())->method('error');
        $this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId);
    }

    /**
     * @dataProvider isAlmaBusinessDataDataProvider
     *
     * @return void
     */
    public function testIsAlmaBusinessDataExistByCart($almaBusinessData, $expected)
    {
        $this->almabusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with(1)
            ->willReturn($almaBusinessData);
        $this->assertEquals($expected, $this->almaBusinessDataService->isAlmaBusinessDataExistByCart(1));
    }

    /**
     * @return array
     */
    public function isAlmaBusinessDataDataProvider()
    {
        return [
            'with almaBusinessData' => [
                [
                    'id_alma_business_data' => '5',
                    'id_cart' => '3',
                    'id_order' => '2',
                    'alma_payment_id' => 'alma_payment_id',
                    'is_bnpl_eligible' => '1',
                    'plan_key' => '',
                ],
                true,
            ],
            'array vide almaBusinessData' => [
                [],
                false,
            ],

            'false almaBusinessData' => [
                false,
                false,
            ],
        ];
    }

    /**
     * @dataProvider eligibilityPlanDataProvider
     */
    public function testIsBnplEligible($plans, $expected)
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('is_bnpl_eligible', $expected, 'id_cart = ' . $cartId);

        $this->assertNull($this->almaBusinessDataService->saveIsBnplEligible($plans, $cartId));
    }

    /**
     * @return void
     */
    public function testUpdateIsBnplEligible()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('is_bnpl_eligible', true, 'id_cart = ' . $cartId);
        $this->assertNull($this->almaBusinessDataService->updateIsBnplEligible(true, $cartId));
    }

    /**
     * @return void
     */
    public function testUpdatePlanKey()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('plan_key', true, 'id_cart = ' . $cartId);
        $this->assertNull($this->almaBusinessDataService->updatePlanKey(true, $cartId));
    }

    /**
     * @return void
     */
    public function testUpdateOrderId()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('id_order', false, 'id_cart = ' . $cartId);
        $this->assertNull($this->almaBusinessDataService->updateOrderId(false, $cartId));
    }

    /**
     * @return void
     */
    public function testUpdateAlmaPaymentId()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('update')
            ->with('alma_payment_id', true, 'id_cart = ' . $cartId);
        $this->assertNull($this->almaBusinessDataService->updateAlmaPaymentId(true, $cartId));
    }

    /**
     * @return array[]
     */
    public function eligibilityPlanDataProvider()
    {
        return [
            'No plans' => [
                [],
                false,
            ],
            'Pay now only' => [
                [
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 1,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                ],
                false,
            ],
            'Pay now and bnpl' => [
                [
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 1,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 2,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 1,
                        'deferred_days' => 15,
                        'deferred_months' => 0,
                    ]),
                ],
                true,
            ],
            'Pay now eligible and bnpl not eligible' => [
                [
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 1,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => false,
                        'installments_count' => 2,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => false,
                        'installments_count' => 1,
                        'deferred_days' => 15,
                        'deferred_months' => 0,
                    ]),
                ],
                false,
            ],
            'Pay now not eligible and bnpl eligible' => [
                [
                    new Eligibility([
                        'eligible' => false,
                        'installments_count' => 1,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 2,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 1,
                        'deferred_days' => 15,
                        'deferred_months' => 0,
                    ]),
                ],
                true,
            ],
            'Pay now eligible and p2x not eligible' => [
                [
                    new Eligibility([
                        'eligible' => false,
                        'installments_count' => 1,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => false,
                        'installments_count' => 2,
                        'deferred_days' => 0,
                        'deferred_months' => 0,
                    ]),
                    new Eligibility([
                        'eligible' => true,
                        'installments_count' => 1,
                        'deferred_days' => 15,
                        'deferred_months' => 0,
                    ]),
                ],
                true,
            ],
        ];
    }
}
