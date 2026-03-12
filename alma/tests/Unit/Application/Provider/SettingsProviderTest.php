<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Provider;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FieldsMock;

class SettingsProviderTest extends TestCase
{
    /**
     * @var AuthenticationSettingsProvider
     */
    private AuthenticationSettingsProvider $authenticationSettingsProvider;

    public function setup(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->module->name = 'alma';
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->authenticationSettingsProvider = new AuthenticationSettingsProvider(
            $this->module,
            $this->settingsRepository,
            $this->languageRepository,
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
        $this->assertEquals($apiKeysFromRepository, $this->authenticationSettingsProvider->getApiKeys());
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
            ->willReturnOnConsecutiveCalls(EncryptorHelper::OBSCURE_VALUE, '', '');
        $this->assertEquals($apiKeysFromRepository, $this->authenticationSettingsProvider->getApiKeys());
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
            ->willReturnOnConsecutiveCalls(EncryptorHelper::OBSCURE_VALUE, 'live_api_key', 'live_api_key');
        $this->assertEquals($apiKeysFromRepository, $this->authenticationSettingsProvider->getApiKeys());
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
            ->willReturnOnConsecutiveCalls(EncryptorHelper::OBSCURE_VALUE, EncryptorHelper::OBSCURE_VALUE);
        $this->assertEquals($apiKeysFromRepository, $this->authenticationSettingsProvider->getApiKeys());
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
        $this->assertEquals($apiKeysFromPost, $this->authenticationSettingsProvider->getApiKeys());
    }

    public function testGetAllFieldsWithoutMultiLanguageKeyExploded()
    {
        $allFields = array_merge(
            FieldsMock::fieldsWithLangFalse(),
            FieldsMock::fieldsWithoutLang(),
        );
        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en', 'language_code' => 'en-us', 'locale' => 'en-US']
        ];
        $expected = array_merge(
            FieldsMock::fieldsWithLangFalse(),
            FieldsMock::fieldsWithoutLang(),
        );

        $this->languageRepository->expects($this->never())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->assertEquals($expected, $this->authenticationSettingsProvider->getSplitLanguageFields($allFields));
    }

    public function testGetAllValuesWithLanguageKeyExploded()
    {
        $allFields = array_merge(
            FieldsMock::fieldsWithLangTrue(),
            FieldsMock::fieldsWithoutLang()
        );

        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en', 'language_code' => 'en-us', 'locale' => 'en-US'],
            ['id_lang' => 2, 'iso_code' => 'fr', 'language_code' => 'fr-fr', 'locale' => 'fr-FR']
        ];

        $expected = array_merge(
            FieldsMock::fieldsWithLangTrueExpected('classic_field_lang_true_1'),
            FieldsMock::fieldsWithLangTrueExpected('classic_field_lang_true_2'),
            FieldsMock::fieldsWithoutLang()
        );
        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->assertEquals($expected, $this->authenticationSettingsProvider->getSplitLanguageFields($allFields));
    }
}
