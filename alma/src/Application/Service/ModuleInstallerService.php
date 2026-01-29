<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Language;
use Tab;

class ModuleInstallerService
{
    private const HOOK_LIST = [
        'actionFrontControllerSetMedia', // Hook used for load assets
    ];

    private const TABS = [
        [
            'label' => 'Alma',
            'class_name' => 'ALMA',
            'parent' => 0,
            'icon' => null
        ],
        [
            'label' => 'Settings',
            'class_name' => 'AdminAlmaSettings',
            'parent' => 'ALMA',
            'icon' => 'tune'
        ]
    ];

    /**
     * @var ModuleService
     */
    private ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    public function installTabs($tabs): bool
    {
        foreach ($tabs as $tabData) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $tabData['class_name'];
            $tab->icon = $tabData['icon'];
            $tab->module = $this->moduleService->getModule()->name;
            if ($tabData['parent'] === 0) {
                $tab->id_parent = 0;
            } else {
                $tab->id_parent = (int) Tab::getIdFromClassName($tabData['parent']);
            }
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $tabData['label'];
            }
            $tab->wording = $tabData['label'];
            $tab->wording_domain = 'Modules.Alma.Admin';

            if (!$tab->add()) {
                return false;
            }
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
        return $this->moduleService->registerHooks(self::HOOK_LIST)
            && $this->installTabs(self::TABS);
    }
}
