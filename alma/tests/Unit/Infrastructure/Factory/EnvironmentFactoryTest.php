<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Factory;

use Alma\Client\Domain\ValueObject\Environment;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Factory\EnvironmentFactory;

class EnvironmentFactoryTest extends TestCase
{
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $settingsProvider;

    public function setUp(): void
    {
        $this->settingsProvider = $this->createMock(SettingsProvider::class);
        $this->environmentFactory = new EnvironmentFactory(
            $this->settingsProvider
        );
    }

    public function testCreate(): void
    {
        $this->settingsProvider->expects($this->once())
            ->method('getEnvironment')
            ->willReturn('test');

        $this->assertInstanceOf(Environment::class, $this->environmentFactory->create());
    }
}
