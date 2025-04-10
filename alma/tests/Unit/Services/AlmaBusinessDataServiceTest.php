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

use Alma\API\Client;
use Alma\API\Endpoints\Merchants;
use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\DTO\MerchantBusinessEvent\CartInitiatedBusinessEvent;
use Alma\API\Entities\DTO\MerchantBusinessEvent\OrderConfirmedBusinessEvent;
use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\PrestaShop\Model\AlmaBusinessDataModel;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Model\LoggerPsr1;
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
    protected $almaBusinessDataModelMock;
    /**
     * @var \Alma\PrestaShop\Factories\LoggerFactory
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
     * @var \Alma\PrestaShop\Repositories\AlmaBusinessDataRepository
     */
    protected $almaBusinessDataRepositoryMock;
    /**
     * @var \Alma\API\Entities\DTO\MerchantBusinessEvent\OrderConfirmedBusinessEvent
     */
    protected $orderConfirmedBusinessEventMock;
    /**
     * @var Client
     */
    protected $clientMock;
    /**
     * @var \Alma\API\Endpoints\Merchants
     */
    protected $merchantsMock;

    public function setUp()
    {
        $this->merchantsMock = $this->createMock(Merchants::class);
        $this->clientMock = $this->createMock(Client::class);
        $this->clientMock->merchants = $this->merchantsMock;
        $this->clientModelMock = $this->createMock(ClientModel::class);
        $this->loggerMock = $this->createMock(LoggerPsr1::class);
        $this->almaBusinessDataModelMock = $this->createMock(AlmaBusinessDataModel::class);
        $this->almaBusinessDataRepositoryMock = $this->createMock(AlmaBusinessDataRepository::class);
        $this->almaBusinessDataService = new AlmaBusinessDataService(
            $this->clientModelMock,
            $this->loggerMock,
            $this->almaBusinessDataModelMock,
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
            ->method('updateByCartId')
            ->with('id_order', $orderId, $cartId);
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with($cartId)
            ->willReturn($almaBusinessData);
        $this->clientModelMock->expects($this->once())
            ->method('getClient')
            ->willReturn($this->clientMock);
        $this->merchantsMock->expects($this->once())
            ->method('sendOrderConfirmedBusinessEvent')
            ->with($this->isInstanceOf(OrderConfirmedBusinessEvent::class));
        $this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId);
    }

    /**
     * @dataProvider wrongDataOrderConfirmedBusinessEventDataProvider
     *
     * @return void
     * @throws \PrestaShopException
     */
    public function testRunOrderConfirmedBusinessEventWithWrongParamLoggerErrorNoThrow($almaBusinessData, $orderId, $cartId)
    {
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('updateByCartId')
            ->with('id_order', $orderId, $cartId);
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with($cartId)
            ->willReturn($almaBusinessData);
        $this->clientModelMock->expects($this->never())
            ->method('getClient');
        $this->merchantsMock->expects($this->never())
            ->method('sendOrderConfirmedBusinessEvent');
        $this->loggerMock->expects($this->once())->method('error');
        $this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId);
    }

    /**
     * @throws \PrestaShopException
     */
    public function testRunOrderConfirmedBusinessEventWithoutOrderId()
    {
        $orderId = null;
        $cartId = '3';
        $this->almaBusinessDataRepositoryMock->expects($this->never())
        ->method('updateByCartId');
        $this->clientModelMock->expects($this->never())
            ->method('getClient');
        $this->merchantsMock->expects($this->never())
            ->method('sendOrderConfirmedBusinessEvent');
        $this->loggerMock->expects($this->once())->method('error');
        $this->assertNull($this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId));
    }

    /**
     * @throws \PrestaShopException
     */
    public function testRunOrderConfirmedBusinessEventWithoutCartId()
    {
        $orderId = 5;
        $cartId = 0;
        $this->almaBusinessDataRepositoryMock->expects($this->never())
            ->method('updateByCartId');
        $this->clientModelMock->expects($this->never())
            ->method('getClient');
        $this->merchantsMock->expects($this->never())
            ->method('sendOrderConfirmedBusinessEvent');
        $this->loggerMock->expects($this->once())->method('error');
        $this->assertNull($this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId));
    }

    /**
     * @return array[]
     */
    public function wrongDataOrderConfirmedBusinessEventDataProvider()
    {
        return [
            'almaPaymentId is empty' => [
                [
                    'id_alma_business_data' => '5',
                    'id_cart' => '3',
                    'id_order' => '2',
                    'alma_payment_id' => '',
                    'is_bnpl_eligible' => '1',
                    'plan_key' => 'general_2_0_0',
                ],
                2,
                3,
            ],
            'plan_key empty with almaPaymentId not empty (isBNPL false with almaPaymentId not empty)' => [
                [
                    'id_alma_business_data' => '5',
                    'id_cart' => '3',
                    'id_order' => '2',
                    'alma_payment_id' => 'alma_payment_id',
                    'is_bnpl_eligible' => '1',
                    'plan_key' => '',
                ],
                2,
                3,
            ],
        ];
    }

    /**
     * @return void
     */
    public function testRunOrderConfirmedBusinessEventWithClientIssueLoggerErrorNoThrow()
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
            ->method('updateByCartId')
            ->with('id_order', $orderId, $cartId);
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with($cartId)
            ->willReturn($almaBusinessData);
        $this->clientModelMock->expects($this->once())
            ->method('getClient')
            ->willReturn($this->clientMock);
        $this->merchantsMock->expects($this->once())
            ->method('sendOrderConfirmedBusinessEvent')
            ->willThrowException(new RequestException());
        $this->loggerMock->expects($this->once())->method('error');
        $this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId);
    }

    /**
     * @return void
     */
    public function testRunOrderConfirmedBusinessEventWithGetByCartIdReturnFalse()
    {
        $orderId = '2';
        $cartId = '3';
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('updateByCartId')
            ->with('id_order', $orderId, $cartId);
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with($cartId)
            ->willReturn(false);
        $this->clientModelMock->expects($this->never())
            ->method('getClient');
        $this->merchantsMock->expects($this->never())
            ->method('sendOrderConfirmedBusinessEvent');
        $this->loggerMock->expects($this->never())->method('error');
        $this->assertNull($this->almaBusinessDataService->runOrderConfirmedBusinessEvent($orderId, $cartId));
    }

    /**
     * @return void
     */
    public function testRunCartInitiatedBusinessEvent()
    {
        $cartId = '1';
        $this->clientModelMock->expects($this->once())
            ->method('getClient')
            ->willReturn($this->clientMock);
        $this->merchantsMock->expects($this->once())
            ->method('sendCartInitiatedBusinessEvent')
            ->with($this->isInstanceOf(CartInitiatedBusinessEvent::class));
        $this->almaBusinessDataService->runCartInitiatedBusinessEvent($cartId);
    }

    /**
     * @dataProvider wrongParamCartInitiatedBusinessEventDataProvider
     *
     * @return void
     */
    public function testRunCartInitiatedBusinessEventWithWrongParamLoggerErrorNoThrow($cartId)
    {
        $this->clientModelMock->expects($this->never())
            ->method('getClient');
        $this->merchantsMock->expects($this->never())
            ->method('sendCartInitiatedBusinessEvent');
        $this->loggerMock->expects($this->once())->method('error');
        $this->almaBusinessDataService->runCartInitiatedBusinessEvent($cartId);
    }

    /**
     * @return void
     */
    public function testRunCartInitiatedBusinessEventWithClientIssueLoggerErrorNoThrow()
    {
        $cartId = '1';
        $this->clientModelMock->expects($this->once())
            ->method('getClient')
            ->willReturn($this->clientMock);
        $this->merchantsMock->expects($this->once())
            ->method('sendCartInitiatedBusinessEvent')
            ->willThrowException(new RequestException());
        $this->loggerMock->expects($this->once())->method('error');
        $this->almaBusinessDataService->runCartInitiatedBusinessEvent($cartId);
    }

    /**
     * @return array
     */
    public function wrongParamCartInitiatedBusinessEventDataProvider()
    {
        return [
            'data is empty string' => [
                '',
            ],
            'data is array' => [
                [],
            ],
            'data is null' => [
                null,
            ],
            'data is bool' => [
                false,
            ],
            'data is int' => [
                1,
            ],
            'data is float' => [
                1.1,
            ],
            'data is object' => [
                new \stdClass(),
            ],
        ];
    }

    /**
     * @dataProvider isAlmaBusinessDataDataProvider
     *
     * @return void
     */
    public function testIsAlmaBusinessDataExistByCart($almaBusinessData, $expected)
    {
        $this->almaBusinessDataModelMock->expects($this->once())
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
            ->method('updateByCartId')
            ->with('is_bnpl_eligible', $expected, $cartId);

        $this->assertNull($this->almaBusinessDataService->saveBnplEligibleStatus($plans, $cartId));
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testUpdateBnplEligibleStatus()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('updateByCartId')
            ->with('is_bnpl_eligible', true, $cartId);
        $this->assertNull($this->almaBusinessDataService->updateBnplEligibleStatus(true, $cartId));
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testUpdatePlanKey()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('updateByCartId')
            ->with('plan_key', true, $cartId);
        $this->assertNull($this->almaBusinessDataService->updatePlanKey(true, $cartId));
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testUpdateOrderId()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('updateByCartId')
            ->with('id_order', false, $cartId);
        $this->assertNull($this->almaBusinessDataService->updateOrderId(false, $cartId));
    }

    /**
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function testUpdateAlmaPaymentId()
    {
        $cartId = 1;
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('updateByCartId')
            ->with('alma_payment_id', true, $cartId);
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

    /**
     * @dataProvider almaBusinessDataTableDataProvider
     *
     * @return void
     */
    public function testIsAlmaBusinessDataTableExist($table, $expected)
    {
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('checkIfTableExist')
            ->willReturn($table);
        $this->assertEquals($expected, $this->almaBusinessDataService->isAlmaBusinessDataTableExist());
    }

    /**
     * @dataProvider almaBusinessDataTableDataProvider
     *
     * @return void
     */
    public function testCreateTableIfNotExist($table, $tableExist)
    {
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('checkIfTableExist')
            ->willReturn($table);
        $expectCreateTableIfNotExist = $tableExist ? $this->never() : $this->once();
        $this->almaBusinessDataRepositoryMock->expects($expectCreateTableIfNotExist)
            ->method('createTable');
        $this->assertNull($this->almaBusinessDataService->createTableIfNotExist());
    }

    /**
     * @return void
     */
    public function testCreateTableIfNotExistWithThrowPrestashopException()
    {
        $this->almaBusinessDataRepositoryMock->expects($this->once())
            ->method('createTable')
            ->willThrowException(new \PrestaShopException());
        $this->loggerMock->expects($this->once())->method('warning');
        $this->almaBusinessDataService->createTableIfNotExist();
    }

    /**
     * @return array[]
     */
    public function almaBusinessDataTableDataProvider()
    {
        return [
            'table exist' => [
                [
                    ['Tables_in_prestashop' => 'ps_alma_business_data'],
                ],
                true,
            ],
            'table not exist' => [
                [],
                false,
            ],
        ];
    }

    /**
     * @return void
     * @throws \Alma\API\Exceptions\ParametersException
     */
    public function testGetAlmaPaymentIdByCartIdWithThrowException()
    {
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with(7)
            ->willReturn(false);
        $this->expectException(ParametersException::class);
        $this->almaBusinessDataService->getAlmaPaymentIdByCartId(7);
    }

    /**
     * @dataProvider almaPaymentIdWithWrongDataProvider
     * @param $almaBusinessData
     * @param $cartId
     * @return void
     * @throws \Alma\API\Exceptions\ParametersException
     */
    public function testGetAlmaPaymentIdByCartIdWithWrongCartIdThrowException($almaBusinessData, $cartId)
    {
        $this->almaBusinessDataModelMock->expects($this->never())
            ->method('getByCartId');
        $this->expectException(ParametersException::class);
        $this->almaBusinessDataService->getAlmaPaymentIdByCartId($cartId);
    }

    /**
     * @return void
     * @throws \Alma\API\Exceptions\ParametersException
     */
    public function testGetAlmaPaymentIdByCartIdWithoutAlmaBusinessId()
    {
        $almaBusinessData = [
            'id_alma_business_data' => '1',
            'id_cart' => '5',
            'id_order' => '2',
            'is_bnpl_eligible' => '1',
            'plan_key' => 'general_3_0_0',
        ];
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with(5)
            ->willReturn($almaBusinessData);
        $this->expectException(ParametersException::class);
        $this->assertNull($this->almaBusinessDataService->getAlmaPaymentIdByCartId(5));
    }

    /**
     * @return void
     * @throws \Alma\API\Exceptions\ParametersException
     */
    public function testGetAlmaPaymentIdByCartIdWithAlmaBusinessIdEmpty()
    {
        $almaBusinessData = [
            'id_alma_business_data' => '1',
            'id_cart' => '5',
            'id_order' => '2',
            'alma_payment_id' => null,
            'is_bnpl_eligible' => '1',
            'plan_key' => 'general_3_0_0',
        ];
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with(5)
            ->willReturn($almaBusinessData);
        $this->expectException(ParametersException::class);
        $this->assertNull($this->almaBusinessDataService->getAlmaPaymentIdByCartId(5));
    }

    public function testGetAlmaPaymentIdByCartIdWithRightValue()
    {
        $almaBusinessData = [
            'id_alma_business_data' => '1',
            'id_cart' => '5',
            'id_order' => '2',
            'alma_payment_id' => 'alma_payment_id',
            'is_bnpl_eligible' => '1',
            'plan_key' => 'general_3_0_0',
        ];
        $this->almaBusinessDataModelMock->expects($this->once())
            ->method('getByCartId')
            ->with(5)
            ->willReturn($almaBusinessData);
        $this->assertEquals('alma_payment_id', $this->almaBusinessDataService->getAlmaPaymentIdByCartId(5));
    }

    /**
     * @return array[]
     */
    public function almaPaymentIdWithWrongDataProvider()
    {
        return [
            'return exception with Cart Id array' => [
                false,
                [],
            ],
            'return exception with Cart Id object' => [
                false,
                new \stdClass(),
            ],
            'return exception with Cart Id bool' => [
                false,
                true,
            ],
            'return exception with Cart Id string' => [
                false,
                'string',
            ]
        ];
    }
}
