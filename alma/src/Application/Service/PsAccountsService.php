<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class PsAccountsService
{
    /**
     * @var \PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts
     */
    private PsAccounts $psAccountsFacade;
    /**
     * @var \PrestaShop\PsAccountsInstaller\Installer\Installer
     */
    private Installer $psAccountsInstaller;

    public function __construct(
        PsAccounts $psAccountsFacade,
        Installer $psAccountsInstaller
    ) {
        $this->psAccountsFacade = $psAccountsFacade;
        $this->psAccountsInstaller = $psAccountsInstaller;
    }

    /**
     * @return string
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException
     */
    public function getAccountsCdn(): string
    {
        /* @var \PrestaShop\Module\PsAccounts\Service\PsAccountsService $psAccountsService */
        try {
            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            try {
                $this->psAccountsInstaller->install();
            } catch (\Exception $e) {
                throw new PsAccountsException('Unable to install PsAccounts module: ' . $e->getMessage());
            }

            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();
        }

        try {
            return $psAccountsService->getAccountsCdn();
        } catch (\Exception $e) {
            throw new PsAccountsException('Unable to get PsAccounts CDN: ' . $e->getMessage());
        }
    }

    /**
     * @return object
     * @throws PsAccountsException
     */
    public function getPsAccountsPresenter(): object
    {
        try {
            return $this->psAccountsFacade->getPsAccountsPresenter();
        } catch (ModuleNotInstalledException|ModuleVersionException $e) {
            throw new PsAccountsException('Unable to get PsAccounts presenter: ' . $e->getMessage());
        }
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\PsAccountsException
     */
    public function isAccountLinked(): bool
    {
        try {
            /** @var \PrestaShop\Module\PsAccounts\Service\PsAccountsService $psAccountsService */
            $psAccountsService = $this->psAccountsFacade->getPsAccountsService();
            return $psAccountsService->isAccountLinked();
        } catch (\Exception $e) {
            throw new PsAccountsException('Unable to get link information PsAccounts module: ' . $e->getMessage());
        }
    }
}
