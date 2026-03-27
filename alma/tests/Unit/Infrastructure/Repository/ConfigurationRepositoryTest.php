<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class ConfigurationRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->configurationRepository = new ConfigurationRepository();
    }

    public function testGetMode()
    {
        $this->assertIsString($this->configurationRepository->getMode());
    }

    public function testGetMerchantId()
    {
        $this->assertIsString($this->configurationRepository->getMerchantId());
    }

    public function testGetFeePlanList()
    {
        $this->assertIsArray($this->configurationRepository->getFeePlanList());
    }

    public function testGetCartWidgetDisplayNotEligible()
    {
        $this->assertIsBool($this->configurationRepository->getCartWidgetDisplayNotEligible());
    }
}
