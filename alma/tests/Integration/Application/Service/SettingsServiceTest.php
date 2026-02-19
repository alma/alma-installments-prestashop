<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

use Alma\Client\Domain\Entity\Merchant;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Exception\SettingsServiceException;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        $this->settings = $this->createMock(SettingsRepository::class);
        $this->merchant = $this->createMock(Merchant::class);
        $this->settingsService = new SettingsService(
            $this->authenticationService,
            $this->settings
        );
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
        $this->settings->expects($this->never())
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
        $this->settings->expects($this->once())
            ->method('save');

        $this->settingsService->save();
    }
}
