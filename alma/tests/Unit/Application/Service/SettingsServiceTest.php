<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Exception\SettingsServiceException;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
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
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsServiceException
     */
    public function testDontSaveAuthenticationFailExpectException(): void
    {
        $this->authenticationService->expects($this->once())
            ->method('isValidKey')
            ->willThrowException(new AuthenticationException());
        $this->expectException(SettingsServiceException::class);
        $this->settingsRepository->expects($this->never())
            ->method('save');

        $this->settingsService->save();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsServiceException
     */
    public function testSaveAuthenticationFine(): void
    {
        $this->authenticationService->expects($this->once())
            ->method('isValidKey');
        $this->settingsRepository->expects($this->once())
            ->method('save');

        $this->settingsService->save();
    }
}
