<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Db;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class ModuleInstallerService
{
    private const HOOK_LIST = [
        'actionFrontControllerSetMedia', // Hook used for load assets
    ];

    public const TABS = [
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
    /**
     * @var Installer
     */
    private Installer $psAccountsInstaller;

    public function __construct(
        ModuleService $moduleService,
        Db $dbInstance,
        Installer $psAccountsInstallerService
    ) {
        $this->moduleService = $moduleService;
        $this->dbInstance = $dbInstance;
        $this->psAccountsInstaller = $psAccountsInstallerService;
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
        try {
            return $this->moduleService->registerHooks(self::HOOK_LIST)
                && $this->moduleService->installTabs(self::TABS)
                && $this->installDB()
                && $this->psAccountsInstaller->install();
        } catch (\Exception $e) {
            return false;
        }
    }
}
