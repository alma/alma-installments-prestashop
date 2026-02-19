<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

use Alma\Client\Domain\Entity\Merchant;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\SettingsServiceException;
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
