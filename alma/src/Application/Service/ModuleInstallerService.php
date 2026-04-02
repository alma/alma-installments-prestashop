<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Db;
use PrestaShop\PsAccountsInstaller\Installer\Installer;
use PrestaShopBundle\Translation\TranslatorInterface;

class ModuleInstallerService
{
    private const HOOK_LIST = [
        'actionFrontControllerSetMedia', // Hook used for load assets
        'displayShoppingCartFooter', // Hook used for display widget in the cart page
        'displayProductPriceBlock', // Hook used for display widget in the product page
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
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        ModuleService $moduleService,
        Db $dbInstance,
        Installer $psAccountsInstallerService,
        TranslatorInterface $translator
    ) {
        $this->moduleService = $moduleService;
        $this->dbInstance = $dbInstance;
        $this->psAccountsInstaller = $psAccountsInstallerService;
        $this->translator = $translator;
    }

    public function tabs(): array
    {
        return [
            [
                'label' => 'Alma',
                'class_name' => 'ALMA',
                'icon' => null
            ],
            [
                'label' => $this->translator->trans('Settings', [], 'Modules.Alma.Admin'),
                'class_name' => 'AdminAlmaSettings',
                'parent_class_name' => 'ALMA',
                'icon' => 'tune'
            ],
            [
                'label' => $this->translator->trans('Excluded Categories', [], 'Modules.Alma.Admin'),
                'class_name' => 'AdminAlmaExcludedCategories',
                'parent_class_name' => 'ALMA',
                'route_name' => 'alma_excluded_categories',
                'icon' => 'not_interested'
            ]
        ];
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
                && $this->moduleService->installTabs($this->tabs())
                && $this->installDB()
                && $this->psAccountsInstaller->install();
        } catch (\Exception $e) {
            return false;
        }
    }
}
