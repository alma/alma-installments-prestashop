<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Repository\OrderStateRepository;

class OrderStateRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->language = $this->createMock(\Language::class);
        $this->language->id = 1;
        $this->context->language = $this->language;
        $this->orderStateRepository = new OrderStateRepository($this->context);
    }

    public function testGetAllOrderStates(): void
    {
        $this->assertIsArray($this->orderStateRepository->getOrderStates());
    }
}
