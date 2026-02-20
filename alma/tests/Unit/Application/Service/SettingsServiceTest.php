<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Exception\SettingsException;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->settingsService = new SettingsService(
            $this->authenticationService,
            $this->settingsRepository,
            $this->configurationRepository,
            $this->toolsProxy
        );
    }

    /**
     * TODO: Need to add for each real field add in the form. We probably need to improve this test
     */
    public function testGetFieldsValue(): void
    {
        $this->configurationRepository->expects($this->any())
            ->method('get')
            ->willReturnOnConsecutiveCalls('value1', 'value2', 'value3', 'value4', 'value5');
        $this->toolsProxy->expects($this->any())
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('value1', 'value2', 'value3', 'value4', 'value5');
        $this->assertIsArray($this->settingsService->getFieldsValue());
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsException
     */
    public function testDontSaveAuthenticationFailExpectException(): void
    {
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willThrowException(new AuthenticationException());
        $this->expectException(SettingsException::class);
        $this->settingsRepository->expects($this->never())
            ->method('save');

        $this->settingsService->save();
    }

    public function testSaveAuthenticationWrongWithDifferentMerchantIds(): void
    {
        $merchantIds = [
            'test' => '42',
            'live' => '43'
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds)
            ->willThrowException(new AuthenticationException());
        $this->settingsRepository->expects($this->never())
            ->method('save');
        $this->expectException(SettingsException::class);

        $this->settingsService->save();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsException
     */
    public function testSaveAuthenticationFine(): void
    {
        $merchantIds = [
            'test' => '42',
            'live' => '42'
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds);
        $this->settingsRepository->expects($this->once())
            ->method('getEnvironment')
            ->willReturn('test');
        $this->toolsProxy->expects($this->once())
            ->method('getValue')
            ->with(ApiAdminForm::KEY_FIELD_MODE, 'test')
            ->willReturn('test');
        $this->settingsRepository->expects($this->once())
            ->method('save');

        $this->settingsService->save();
    }
}
