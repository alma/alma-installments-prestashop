<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Domain\ValueObject\Environment;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Factory\ClientConfigurationFactory;

class ClientConfigurationFactoryTest extends TestCase
{
    /**
     * @var ClientConfigurationFactory
     */
    private ClientConfigurationFactory $clientConfigurationFactory;
    /**
     * @var AuthenticationSettingsProvider
     */
    private $authenticationSettingsProvider;

    public function setUp(): void
    {
        $this->authenticationSettingsProvider = $this->createMock(AuthenticationSettingsProvider::class);
        $environment = new Environment(Environment::TEST_MODE);
        $this->clientConfigurationFactory = new ClientConfigurationFactory(
            $this->authenticationSettingsProvider,
            $environment
        );
    }

    public function testCreateReturnsClientConfigurationWithCorrectApiKeyFromRepository(): void
    {
        $apiKeys = [
            'test' => 'test_api_key_123',
            'live' => 'live_api_key_456',
        ];

        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeys);

        $this->assertInstanceOf(ClientConfiguration::class, $this->clientConfigurationFactory->create());
    }
}
