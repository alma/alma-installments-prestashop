<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsRepositoryTest extends TestCase
{
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settings;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configuration;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $tools;
    /**
     * @var EncryptorHelper
     */
    private EncryptorHelper $encryptorHelper;

    public function setUp(): void
    {
        $this->configuration = $this->createMock(ConfigurationRepository::class);
        $this->tools = $this->createMock(ToolsProxy::class);
        $this->encryptorHelper = $this->createMock(EncryptorHelper::class);
        $this->settings = new SettingsRepository(
            $this->configuration,
            $this->tools,
            $this->encryptorHelper
        );
    }

    public function testGetApiKeysWithTestValueAndLiveNull(): void
    {
        $expectedApiKeys = [
            'test' => 'decrypted_test_api_key',
            'live' => '',
        ];
        $this->configuration->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([ApiAdminForm::KEY_FIELD_TEST_API_KEY], [ApiAdminForm::KEY_FIELD_LIVE_API_KEY])
            ->willReturnOnConsecutiveCalls('test_api_key', '');
        $this->encryptorHelper->expects($this->once())
            ->method('decrypt')
            ->with('test_api_key')
            ->willReturn('decrypted_test_api_key');
        $this->assertEquals($expectedApiKeys, $this->settings->getApiKeys());
    }

    public function testGetApiKeysWithBothValues(): void
    {
        $expectedApiKeys = [
            'test' => 'decrypted_test_api_key',
            'live' => 'decrypted_live_api_key',
        ];
        $this->configuration->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([ApiAdminForm::KEY_FIELD_TEST_API_KEY], [ApiAdminForm::KEY_FIELD_LIVE_API_KEY])
            ->willReturnOnConsecutiveCalls('test_api_key', 'live_api_key');
        $this->encryptorHelper->expects($this->exactly(2))
            ->method('decrypt')
            ->withConsecutive(['test_api_key'], ['live_api_key'])
            ->willReturnOnConsecutiveCalls('decrypted_test_api_key', 'decrypted_live_api_key');

        $this->assertEquals($expectedApiKeys, $this->settings->getApiKeys());
    }

    public function testGetApiKeysWithBothEmptyValues(): void
    {
        $expectedApiKeys = [
            'test' => '',
            'live' => '',
        ];
        $this->configuration->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([ApiAdminForm::KEY_FIELD_TEST_API_KEY], [ApiAdminForm::KEY_FIELD_LIVE_API_KEY])
            ->willReturnOnConsecutiveCalls('', '');
        $this->encryptorHelper->expects($this->never())
            ->method('decrypt');

        $this->assertEquals($expectedApiKeys, $this->settings->getApiKeys());
    }

    public function testGetEnvironment(): void
    {
        $this->configuration->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MODE)
            ->willReturn('test');
        $this->assertEquals('test', $this->settings->getEnvironment());
    }

    public function testSaveTwoFieldsWithOneEncryptedValueWeEncryptOnce(): void
    {
        $fields = [
            'field1' => ['encrypted' => true],
            'field2' => ['encrypted' => false],
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls('value1', 'value2');
        $this->encryptorHelper->expects($this->once())
            ->method('encrypt')
            ->with('value1')
            ->willReturn('encrypted_value1');
        $this->configuration->expects($this->exactly(2))
            ->method('updateValue')
            ->withConsecutive(['field1', 'encrypted_value1'], ['field2', 'value2']);
        $this->settings->save($fields);
    }

    public function testSaveTwoFieldsWithTwoEncryptedValuesWeEncryptBoth(): void
    {
        $fields = [
            'field1' => ['encrypted' => true],
            'field2' => ['encrypted' => true],
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls('value1', 'value2');
        $this->encryptorHelper->expects($this->exactly(2))
            ->method('encrypt')
            ->withConsecutive(['value1'], ['value2'])
            ->willReturnOnConsecutiveCalls('encrypted_value1', 'encrypted_value2');
        $this->configuration->expects($this->exactly(2))
            ->method('updateValue')
            ->withConsecutive(['field1', 'encrypted_value1'], ['field2', 'encrypted_value2']);
        $this->settings->save($fields);
    }

    public function testSaveTwoFieldsWithoutEncryptedValuesWeNeverEncrypt(): void
    {
        $fields = [
            'field1' => ['encrypted' => false],
            'field2' => ['encrypted' => false],
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls('value1', 'value2');
        $this->encryptorHelper->expects($this->never())
            ->method('encrypt');
        $this->configuration->expects($this->exactly(2))
            ->method('updateValue')
            ->withConsecutive(['field1', 'value1'], ['field2', 'value2']);
        $this->settings->save($fields);
    }

    public function testSaveTwoFieldsWithTwoEncryptedValuesButObscureWeNeverEncrypt(): void
    {
        $fields = [
            'field1' => ['encrypted' => true],
            'field2' => ['encrypted' => true],
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls(EncryptorHelper::OBSCURE_VALUE, EncryptorHelper::OBSCURE_VALUE);
        $this->encryptorHelper->expects($this->never())
            ->method('encrypt');
        $this->configuration->expects($this->never())
            ->method('updateValue');
        $this->settings->save($fields);
    }

    public function testSaveTwoFieldsWithOneEncryptedValuesButObscureWeNeverEncrypt(): void
    {
        $fields = [
            'field1' => ['encrypted' => true],
            'field2' => ['encrypted' => false],
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls(EncryptorHelper::OBSCURE_VALUE, 'value2');
        $this->encryptorHelper->expects($this->never())
            ->method('encrypt');
        $this->configuration->expects($this->once())
            ->method('updateValue')
            ->with('field2', 'value2');
        $this->settings->save($fields);
    }

    public function testSaveTwoFieldsWithOneEncryptedValuesButNotObscureWeEncryptOnce(): void
    {
        $fields = [
            'field1' => ['encrypted' => false],
            'field2' => ['encrypted' => true],
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls(EncryptorHelper::OBSCURE_VALUE, 'value2');
        $this->encryptorHelper->expects($this->once())
            ->method('encrypt')
            ->with('value2')
            ->willReturn('encrypted_value2');
        $this->configuration->expects($this->once())
            ->method('updateValue')
            ->with('field2', 'encrypted_value2');
        $this->settings->save($fields);
    }

    public function testSaveThreeFieldsWithOneOverrided(): void
    {
        $fields = [
            'field1' => ['encrypted' => false],
            'field2' => ['encrypted' => true],
            'field3' => ['encrypted' => false],
        ];
        $overrideValues = [
            'field3' => 'override_value3',
        ];
        $this->tools->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'], ['field3'])
            ->willReturnOnConsecutiveCalls('value1', 'value2', 'value3');
        $this->encryptorHelper->expects($this->once())
            ->method('encrypt')
            ->with('value2')
            ->willReturn('encrypted_value2');
        $this->configuration->expects($this->exactly(3))
            ->method('updateValue')
            ->withConsecutive(['field1', 'value1'], ['field2', 'encrypted_value2'], ['field3', 'override_value3']);
        $this->settings->save($fields, $overrideValues);
    }
}
