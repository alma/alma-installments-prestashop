<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Factory;

use Alma\Client\Domain\ValueObject\Environment;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Factory\EnvironmentFactory;

class EnvironmentFactoryTest extends TestCase
{
    /**
     * @var AuthenticationSettingsProvider
     */
    private AuthenticationSettingsProvider $authenticationSettingsProvider;

    public function setUp(): void
    {
        $this->authenticationSettingsProvider = $this->createMock(AuthenticationSettingsProvider::class);
        $this->environmentFactory = new EnvironmentFactory(
            $this->authenticationSettingsProvider
        );
    }

    public function testCreate(): void
    {
        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getEnvironment')
            ->willReturn('test');

        $this->assertInstanceOf(Environment::class, $this->environmentFactory->create());
    }
}
