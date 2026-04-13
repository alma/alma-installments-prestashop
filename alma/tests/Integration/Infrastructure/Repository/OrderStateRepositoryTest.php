<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Repository\OrderStateRepository;

class OrderStateRepositoryTest extends TestCase
{
    private const EXPECTED_KEYS = [
        'id_order_state',
        'invoice',
        'send_email',
        'module_name',
        'color',
        'unremovable',
        'hidden',
        'logable',
        'delivery',
        'shipped',
        'paid',
        'pdf_invoice',
        'pdf_delivery',
        'deleted',
        'id_lang',
        'name',
        'template',
    ];

    public function setUp(): void
    {
        $this->context = new \Context();
        $this->language = new \Language();
        $this->language->id = 1;
        $this->context->language = $this->language;
        $this->orderStateRepository = new OrderStateRepository($this->context);
    }

    public function testGetAllOrderStates(): void
    {
        $orderStates = $this->orderStateRepository->getOrderStates();

        $this->assertNotEmpty($orderStates, 'The order states list should not be empty');

        foreach ($orderStates as $orderState) {
            foreach (self::EXPECTED_KEYS as $key) {
                $this->assertArrayHasKey(
                    $key,
                    $orderState,
                    sprintf('The key "%s" is missing in the order state', $key)
                );
            }
        }
    }
}
