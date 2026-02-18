<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Factory;

use Alma\Client\Domain\ValueObject\Environment;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Factory\EnvironmentFactory;

class EnvironmentFactoryTest extends TestCase
{
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;

    public function setUp(): void
    {
        $this->settingsService = $this->createMock(SettingsService::class);
        $this->environmentFactory = new EnvironmentFactory(
            $this->settingsService
        );
    }

    public function testCreate(): void
    {
        $this->settingsService->expects($this->once())
            ->method('getEnvironment')
            ->willReturn('test');

        $this->assertInstanceOf(Environment::class, $this->environmentFactory->create());
    }
}
