<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
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

    public function testGetApiKeysWithApiKeysFromRepository(): void
    {
        $apiKeysFromRepository = [
            'test' => 'test_api_key',
            'live' => 'live_api_key',
        ];
        $this->settingsRepository->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeysFromRepository);
        $this->assertEquals($apiKeysFromRepository, $this->settingsProvider->getApiKeys());
    }

    public function testGetApiKeysWithTestApiKeyFromPostWithObscureValueAndLiveEmpty(): void
    {
        $apiKeysFromRepository = [
            'test' => 'test_api_key',
            'live' => '',
        ];
        $this->settingsRepository->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeysFromRepository);
        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(true);
        $this->toolsProxy->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                [ApiAdminForm::KEY_FIELD_TEST_API_KEY],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY, $apiKeysFromRepository['live']]
            )
            ->willReturnOnConsecutiveCalls(EncryptionHelper::OBSCURE_VALUE, '', '');
        $this->assertEquals($apiKeysFromRepository, $this->settingsProvider->getApiKeys());
    }

    public function testGetApiKeysWithTestApiKeyFromPostWithObscureValueAndLiveApiKey(): void
    {
        $apiKeysFromRepository = [
            'test' => 'test_api_key',
            'live' => 'live_api_key',
        ];
        $this->settingsRepository->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeysFromRepository);
        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(true);
        $this->toolsProxy->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                [ApiAdminForm::KEY_FIELD_TEST_API_KEY],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY, $apiKeysFromRepository['live']]
            )
            ->willReturnOnConsecutiveCalls(EncryptionHelper::OBSCURE_VALUE, 'live_api_key', 'live_api_key');
        $this->assertEquals($apiKeysFromRepository, $this->settingsProvider->getApiKeys());
    }

    public function testGetApiKeysWithBothApiKeyFromPostWithBothObscureValues(): void
    {
        $apiKeysFromRepository = [
            'test' => 'test_api_key',
            'live' => 'live_api_key',
        ];
        $this->settingsRepository->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeysFromRepository);
        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(true);
        $this->toolsProxy->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                [ApiAdminForm::KEY_FIELD_TEST_API_KEY],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY]
            )
            ->willReturnOnConsecutiveCalls(EncryptionHelper::OBSCURE_VALUE, EncryptionHelper::OBSCURE_VALUE);
        $this->assertEquals($apiKeysFromRepository, $this->settingsProvider->getApiKeys());
    }

    public function testGetApiKeysWithApiKeysFromPostWithBothApiKeys(): void
    {
        $apiKeysFromRepository = [
            'test' => 'test_api_key_db',
            'live' => 'live_api_key_db',
        ];
        $apiKeysFromPost = [
            'test' => 'test_api_key_post',
            'live' => 'live_api_key_post',
        ];
        $this->settingsRepository->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($apiKeysFromRepository);
        $this->toolsProxy->expects($this->once())
            ->method('isSubmit')
            ->with('submit' . $this->module->name)
            ->willReturn(true);
        $this->toolsProxy->expects($this->exactly(4))
            ->method('getValue')
            ->withConsecutive(
                [ApiAdminForm::KEY_FIELD_TEST_API_KEY],
                [ApiAdminForm::KEY_FIELD_TEST_API_KEY, $apiKeysFromRepository['test']],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY],
                [ApiAdminForm::KEY_FIELD_LIVE_API_KEY, $apiKeysFromRepository['live']]
            )
            ->willReturnOnConsecutiveCalls(
                'test_api_key_db',
                'test_api_key_post',
                'live_api_key_db',
                'live_api_key_post'
            );
        $this->assertEquals($apiKeysFromPost, $this->settingsProvider->getApiKeys());
    }
}
