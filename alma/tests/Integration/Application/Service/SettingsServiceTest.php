<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

use Alma\Client\Domain\Entity\Merchant;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\SettingsServiceException;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        $this->settings = $this->createMock(SettingsRepository::class);
        $this->module = $this->createMock(\Module::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->merchant = $this->createMock(Merchant::class);
        $this->settingsService = new SettingsService(
            $this->authenticationService,
            $this->settings,
            $this->module,
            $this->toolsProxy
        );
    }

    public function testGetApiKeyWithCorrectApiKeyFromRepository(): void
    {
        $apiKeyFromDb = 'test_api_key';
        $this->settings->expects($this->once())
            ->method('getApiKey')
            ->willReturn($apiKeyFromDb);
        $this->assertEquals($apiKeyFromDb, $this->settingsService->getApiKey());
    }

    public function testGetApiKeyWithCorrectApiKeyFromPostWithObscureValue(): void
    {
        $apiKeyFromDb = 'test_api_key';
        $this->settings->expects($this->once())
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
        $this->assertEquals($apiKeyFromDb, $this->settingsService->getApiKey());
    }

    public function testGetApiKeyWithCorrectApiKeyFromPostWithApiKey(): void
    {
        $apiKeyFromDb = 'test_api_key_db';
        $apiKeyFromPost = 'test_api_key_post';
        $this->settings->expects($this->once())
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
        $this->assertEquals($apiKeyFromPost, $this->settingsService->getApiKey());
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsServiceException
     */
    public function testDontSaveAuthenticationFailExpectException(): void
    {
        $this->authenticationService->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);
        $this->expectException(SettingsServiceException::class);
        $this->settings->expects($this->never())
            ->method('save');

        $this->settingsService->save();
    }

    public function testSaveAuthenticationFine(): void
    {
        $this->authenticationService->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $this->settings->expects($this->once())
            ->method('save');

        $this->settingsService->save();
    }
}
