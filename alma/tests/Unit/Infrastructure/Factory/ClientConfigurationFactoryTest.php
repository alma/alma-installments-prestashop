<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Domain\ValueObject\Environment;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Factory\ClientConfigurationFactory;

class ClientConfigurationFactoryTest extends TestCase
{
    /**
     * @var ClientConfigurationFactory
     */
    private ClientConfigurationFactory $clientConfigurationFactory;
    /**
     * @var SettingsProvider
     */
    private $settingsProvider;

    public function setUp(): void
    {
        $this->settingsProvider = $this->createMock(SettingsProvider::class);
        $environment = new Environment(Environment::TEST_MODE);
        $this->clientConfigurationFactory = new ClientConfigurationFactory(
            $this->settingsProvider,
            $environment
        );
    }

    public function testCreateReturnsClientConfigurationWithCorrectApiKeyFromRepository(): void
    {
        $apiKey = 'test_api_key_123';

        $this->settingsProvider->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKey);

        $this->assertInstanceOf(ClientConfiguration::class, $this->clientConfigurationFactory->create());
    }
}
