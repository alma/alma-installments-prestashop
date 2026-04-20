<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\RefundService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\RefundAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\OrderStateRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

class RefundServiceTest extends TestCase
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->orderStateRepository = $this->createMock(OrderStateRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->refundService = new RefundService(
            $this->context,
            $this->configurationRepository,
            $this->orderStateRepository,
            $this->translator
        );
    }

    public function testRefundStateOrder()
    {
        $orderState = [
            ['id_order_state' => 7, 'name' => 'Refunded'],
            ['id_order_state' => 4, 'name' => 'Shipped'],
        ];
        $expected = [
            'type' => 'select',
            'label' => 'Refund state order',
            'required' => false,
            'form' => 'refund_on_change',
            'encrypted' => false,
            'options' => [
                'desc' => 'Your order state to sync refund with Alma',
                'options' => [
                    'query' => $orderState,
                    'id' => 'id_order_state',
                    'name' => 'name',
                ],
            ],
        ];
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturnOnConsecutiveCalls('Refund state order', 'Your order state to sync refund with Alma');
        $this->orderStateRepository->expects($this->once())
            ->method('getOrderStates')
            ->willReturn($orderState);

        $this->assertEquals($expected, $this->refundService->refundStateOrder());
    }

    public function testDefaultFieldsToSaveFirstSave(): void
    {
        $expected = [
            RefundAdminForm::KEY_FIELD_REFUND_ON_CHANGE_STATE => 1,
            RefundAdminForm::KEY_FIELD_STATE_REFUND_SELECT => 7,
        ];
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');
        $this->assertEquals($expected, $this->refundService->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveUpdateConfiguration(): void
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');
        $this->assertEquals([], $this->refundService->defaultFieldsToSave());
    }
}
