<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class PsAccountsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->psAccountsFacade = $this->createMock(PsAccounts::class);
        $this->psAccountsInstaller = $this->createMock(Installer::class);
        $this->psAccountsService = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getAccountsCdn', 'isAccountLinked'])
            ->getMock();
        $this->almaPsAccountsService = new PsAccountsService(
            $this->psAccountsFacade,
            $this->psAccountsInstaller
        );
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException
     */
    public function testGetAccountsCdnServiceThrowInstallExceptionThrowException()
    {
        $this->psAccountsFacade->expects($this->once())
            ->method('getPsAccountsService')
            ->willThrowException(new InstallerException('Module not installed'));
        $this->psAccountsInstaller->expects($this->once())
            ->method('install')
            ->willThrowException(new \Exception());
        $this->psAccountsService->expects($this->never())
            ->method('getAccountsCdn');
        $this->expectException(PsAccountsException::class);
        $this->almaPsAccountsService->getAccountsCdn();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException
     */
    public function testGetAccountsCdnServiceThrowInstallExceptionExecuteInstallPsAccountsAndCdnThrowException()
    {
        $this->psAccountsFacade->expects($this->exactly(2))
            ->method('getPsAccountsService')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new InstallerException('Module not installed')),
                $this->psAccountsService
            ));
        $this->psAccountsInstaller->expects($this->once())
            ->method('install')
            ->willReturn(true);
        $this->psAccountsService->expects($this->once())
            ->method('getAccountsCdn')
            ->willThrowException(new \Exception());
        $this->expectException(PsAccountsException::class);
        $this->almaPsAccountsService->getAccountsCdn();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException
     */
    public function testGetAccountsCdnServiceThrowInstallExceptionExecuteInstallPsAccountsAndReturnCdn()
    {
        $this->psAccountsFacade->expects($this->exactly(2))
            ->method('getPsAccountsService')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new InstallerException('Module not installed')),
                $this->psAccountsService
            ));
        $this->psAccountsInstaller->expects($this->once())
            ->method('install')
            ->willReturn(true);
        $this->psAccountsService->expects($this->once())
            ->method('getAccountsCdn')
            ->willReturn('https://cdn.example.com/psaccounts.js');
        $this->almaPsAccountsService->getAccountsCdn();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     */
    public function testGetPsAccountsPresenterThrowException()
    {
        $this->psAccountsFacade->expects($this->once())
            ->method('getPsAccountsPresenter')
            ->willThrowException(new ModuleVersionException('Version error'));
        $this->expectException(PsAccountsException::class);
        $this->almaPsAccountsService->getPsAccountsPresenter();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     */
    public function testGetPsAccountsPresenterReturnPresenter()
    {
        $this->psAccountsFacade->expects($this->once())
            ->method('getPsAccountsPresenter')
            ->willReturn(new \stdClass());
        $this->almaPsAccountsService->getPsAccountsPresenter();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     */
    public function testIsAccountLinkedThrowException()
    {
        $this->psAccountsFacade->expects($this->once())
            ->method('getPsAccountsService')
            ->willReturn($this->psAccountsService);
        $this->psAccountsService->expects($this->once())
            ->method('isAccountLinked')
            ->willThrowException(new \Exception());
        $this->expectException(PsAccountsException::class);
        $this->almaPsAccountsService->isAccountLinked();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     */
    public function testIsAccountLinkedReturnTrue()
    {
        $this->psAccountsFacade->expects($this->once())
            ->method('getPsAccountsService')
            ->willReturn($this->psAccountsService);
        $this->psAccountsService->expects($this->once())
            ->method('isAccountLinked')
            ->willReturn(true);
        $this->assertTrue($this->almaPsAccountsService->isAccountLinked());
    }

    public function tearDown(): void
    {
        $this->psAccountsFacade = null;
        $this->psAccountsInstaller = null;
        $this->psAccountsService = null;
        parent::tearDown();
    }
}
