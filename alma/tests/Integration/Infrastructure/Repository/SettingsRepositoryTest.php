<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsRepositoryTest extends TestCase
{
    public function setup(): void
    {
        $this->toolsProxy = new ToolsProxy();
        $this->configurationRepository = new ConfigurationRepository();
        $this->encryptionHelper = $this->createMock(EncryptionHelper::class);
        $this->settingsRepository = new SettingsRepository(
            $this->configurationRepository,
            $this->toolsProxy,
            $this->encryptionHelper
        );
    }

    public function testSave(): void
    {
        $_POST['ALMA_FIELD_TEST_1'] = 'test_value_123';
        $_POST['ALMA_FIELD_TEST_2'] = '1';
        $fields = [
            'ALMA_FIELD_TEST_1' => $_POST['ALMA_FIELD_TEST_1'],
            'ALMA_FIELD_TEST_2' => $_POST['ALMA_FIELD_TEST_2'],
        ];

        $this->settingsRepository->save($fields);

        $this->assertEquals('test_value_123', $this->configurationRepository->get('ALMA_FIELD_TEST_1'));
        $this->assertEquals('1', $this->configurationRepository->get('ALMA_FIELD_TEST_2'));
    }

    protected function tearDown(): void
    {
        $this->configurationRepository->deleteByName('ALMA_FIELD_TEST_1');
        $this->configurationRepository->deleteByName('ALMA_FIELD_TEST_2');

        parent::tearDown();
    }
}
