<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
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
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    public function setUp(): void
    {
        $this->configuration = $this->createMock(ConfigurationRepository::class);
        $this->tools = $this->createMock(ToolsProxy::class);
        $this->encryptionHelper = $this->createMock(EncryptionHelper::class);
        $this->settings = new SettingsRepository(
            $this->configuration,
            $this->tools,
            $this->encryptionHelper
        );
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
        $this->encryptionHelper->expects($this->once())
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
        $this->encryptionHelper->expects($this->exactly(2))
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
        $this->encryptionHelper->expects($this->never())
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
            ->willReturnOnConsecutiveCalls(EncryptionHelper::OBSCURE_VALUE, EncryptionHelper::OBSCURE_VALUE);
        $this->encryptionHelper->expects($this->never())
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
            ->willReturnOnConsecutiveCalls(EncryptionHelper::OBSCURE_VALUE, 'value2');
        $this->encryptionHelper->expects($this->never())
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
            ->willReturnOnConsecutiveCalls(EncryptionHelper::OBSCURE_VALUE, 'value2');
        $this->encryptionHelper->expects($this->once())
            ->method('encrypt')
            ->with('value2')
            ->willReturn('encrypted_value2');
        $this->configuration->expects($this->once())
            ->method('updateValue')
            ->with('field2', 'encrypted_value2');
        $this->settings->save($fields);
    }
}
