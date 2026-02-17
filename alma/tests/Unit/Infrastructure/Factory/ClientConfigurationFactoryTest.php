<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Domain\ValueObject\Environment;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Infrastructure\Factory\ClientConfigurationFactory;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class ClientConfigurationFactoryTest extends TestCase
{
    /**
     * @var ClientConfigurationFactory
     */
    private ClientConfigurationFactory $clientConfigurationFactory;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function setUp(): void
    {
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $environment = new Environment(Environment::TEST_MODE);
        $this->module = $this->createMock(\Module::class);
        $this->module->name = 'alma';
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->clientConfigurationFactory = new ClientConfigurationFactory(
            $this->settingsRepository,
            $environment,
            $this->module,
            $this->toolsProxy
        );
    }

    public function testCreateReturnsClientConfigurationWithCorrectApiKeyFromRepository(): void
    {
        $apiKey = 'test_api_key_123';

        $this->settingsRepository->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKey);

        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(false);

        $this->toolsProxy->expects($this->never())
            ->method('getValue');

        $this->assertInstanceOf(ClientConfiguration::class, $this->clientConfigurationFactory->create());
    }

    public function testCreateReturnsClientConfigurationWithCorrectApiKeyFromPostWithObscureValue(): void
    {
        $apiKeyFromDb = 'test_api_key_db_123';
        $apiKeyFromPost = 'test_api_key_post_123';

        $this->settingsRepository->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKeyFromDb);

        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(true);
        $this->toolsProxy->expects($this->once())
            ->method('getValue')
            ->with('ALMA_TEST_API_KEY')
            ->willReturn(EncryptionHelper::OBSCURE_VALUE);

        $this->assertInstanceOf(ClientConfiguration::class, $this->clientConfigurationFactory->create());
    }

    public function testCreateReturnsClientConfigurationWithCorrectApiKeyFromPostWithApiKey(): void
    {
        $apiKeyFromDb = 'test_api_key_db_123';
        $apiKeyFromPost = 'test_api_key_post_123';

        $this->settingsRepository->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKeyFromDb);

        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(true);
        $this->toolsProxy->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['ALMA_TEST_API_KEY'], ['ALMA_TEST_API_KEY', $apiKeyFromDb])
            ->willReturn($apiKeyFromPost, $apiKeyFromPost);

        $this->assertInstanceOf(ClientConfiguration::class, $this->clientConfigurationFactory->create());
    }
}
