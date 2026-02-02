<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Db;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class ModuleInstallerService
{
    private const HOOK_LIST = [
        'actionFrontControllerSetMedia', // Hook used for load assets
    ];

    private const TABS = [
        [
            'label' => 'Alma',
            'class_name' => 'ALMA',
            'icon' => null
        ],
        [
            'label' => 'Settings',
            'class_name' => 'AdminAlmaSettings',
            'parent_class_name' => 'ALMA',
            'route_name' => 'alma_settings',
            'icon' => 'tune'
        ]
    ];

    /**
     * @var ModuleService
     */
    private ModuleService $moduleService;
    /**
     * @var mixed
     */
    private Db $dbInstance;

    public function __construct(ModuleService $moduleService, Db $dbInstance)
    {
        $this->moduleService = $moduleService;
        $this->dbInstance = $dbInstance;
    }

    /**
     * Create alma database tables
     *
     * @return bool
     */
    public function installDb(): bool
    {
        $sql = file_get_contents($this->moduleService->getLocalPath() . '/sql/install.sql');
        $sql = str_replace(['{_DB_PREFIX_}', '{_MYSQL_ENGINE_}'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);

        if (!$this->dbInstance->execute($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Install the module by :
     * Registering all hooks
     * Create alma database tables
     * Create tabs for menus
     * Check compatibility with PS_account
     *
     * @return bool
     */
    public function install(): bool
    {
        /* @var Installer $psAccountsInstaller  */
        $psAccountsInstaller = $this->moduleService->getService('alma.ps_accounts_installer');
        try {
            return $this->moduleService->registerHooks(self::HOOK_LIST)
                && $this->moduleService->installTabs(self::TABS)
                && $this->installDB()
                && $psAccountsInstaller->install();
        } catch (\Exception $e) {
            return false;
        }
    }
}
