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

    public function testGetFalseReturnEmptyString()
    {
        $this->assertSame('', $this->configurationRepository->get('non_existent_key'));
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

    public function testGetProductWidgetState()
    {
        $this->assertIsBool($this->configurationRepository->getProductWidgetState());
    }

    public function testGetProductWidgetDisplayNotEligible()
    {
        $this->assertIsBool($this->configurationRepository->getProductWidgetDisplayNotEligible());
    }
}
