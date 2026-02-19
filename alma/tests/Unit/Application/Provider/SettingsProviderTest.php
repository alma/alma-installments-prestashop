<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsProviderTest extends TestCase
{
    public function setup(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->module->name = 'alma';
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->settingsProvider = new SettingsProvider(
            $this->module,
            $this->settingsRepository,
            $this->toolsProxy
        );
    }

    public function testGetApiKeyWithCorrectApiKeyFromRepository(): void
    {
        $apiKeyFromDb = 'test_api_key';
        $this->settingsRepository->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKeyFromDb);
        $this->assertEquals($apiKeyFromDb, $this->settingsProvider->getApiKey());
    }

    public function testGetApiKeyWithCorrectApiKeyFromPostWithObscureValue(): void
    {
        $apiKeyFromDb = 'test_api_key';
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
        $this->assertEquals($apiKeyFromDb, $this->settingsProvider->getApiKey());
    }

    public function testGetApiKeyWithCorrectApiKeyFromPostWithApiKey(): void
    {
        $apiKeyFromDb = 'test_api_key_db';
        $apiKeyFromPost = 'test_api_key_post';
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
            ->willReturnOnConsecutiveCalls($apiKeyFromPost, $apiKeyFromPost);
        $this->assertEquals($apiKeyFromPost, $this->settingsProvider->getApiKey());
    }
}
