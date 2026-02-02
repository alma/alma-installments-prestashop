<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;

class PsAccountsService
{
    public const PS_ACCOUNTS_VERSION_REQUIRED = '5.3.0';

    /**
     * @var \PrestaShop\Module\Alma\Application\Service\ModuleService
     */
    private ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * @return object|null
     */
    public function getPsAccountsFacade(): ?object
    {
        return $this->moduleService->getService('alma.ps_accounts_facade');
    }

    /**
     * @return string
     * @throws PsAccountsException
     */
    public function getAccountsCdn(): string
    {
        try {
            /* @var PsAccounts $psAccountsFacade */
            $psAccountsFacade = $this->getPsAccountsFacade();
            $accountsService = $psAccountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            /* @var \PrestaShop\PsAccountsInstaller\Installer\Installer $accountsInstaller */
            $accountsInstaller = $this->moduleService->getService('alma.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsService = $this->getPsAccountsFacade()->getPsAccountsService();
        } catch (\Exception $e) {
            throw new PsAccountsException('Unable to get PsAccounts service: ' . $e->getMessage());
        }

        return $accountsService->getAccountsCdn();
    }

    /**
     * @return object
     * @throws PsAccountsException
     */
    public function getPsAccountsPresenter(): object
    {
        /* @var PsAccounts $psAccountsFacade */
        $psAccountsFacade = $this->getPsAccountsFacade();
        try {
            return $psAccountsFacade->getPsAccountsPresenter();
        } catch (ModuleNotInstalledException|ModuleVersionException $e) {
            throw new PsAccountsException('Unable to get PsAccounts presenter: ' . $e->getMessage());
        }
    }
}
