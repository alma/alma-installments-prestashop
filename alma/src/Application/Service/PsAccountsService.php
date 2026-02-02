<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;

class PsAccountsService
{
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
     * @throws InstallerException
     */
    public function getAccountsCdn(): string
    {
        $accountsService = null;

        try {
            $accountsService = $this->getPsAccountsFacade()->getPsAccountsService();
        } catch (InstallerException $e) {
            $accountsInstaller = $this->moduleService->getService('alma.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsService = $this->getPsAccountsFacade()->getPsAccountsService();
        }

        return $accountsService->getAccountsCdn();
    }

    /**
     * @return object
     */
    public function getPsAccountsPresenter(): object
    {
        return $this->getPsAccountsFacade()->getPsAccountsPresenter();
    }
}
