<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Module;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use Tab;

class ModuleService
{
    /**
     * @var \Alma
     */
    private Module $module;
    /**
     * @var LanguageRepository
     */
    private LanguageRepository $language;

    public function __construct(
        Module $module,
        LanguageRepository $language
    ) {
        $this->module = $module;
        $this->language = $language;
    }

    /**
     * Get the module instance
     *
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Get the local path of the module
     *
     * @return string
     */
    public function getLocalPath(): string
    {
        return $this->module->getLocalPath();
    }

    /**
     * Register one or all hooks needed by the module
     * If $this->module->registerHook return false the module does not install
     *
     * @param $hook string|array
     *
     * @return bool
     */
    public function registerHooks($hook): bool
    {
        if (!$this->module->registerHook($hook)) {
            return false;
        }

        return true;
    }

    /**
     * Install tabs ifor back office menu
     *
     * @param array $tabs
     * @param callable|null $tabFactory
     *
     * @return bool
     */
    public function installTabs(array $tabs, callable $tabFactory = null): bool
    {
        $tabFactory = $tabFactory ?? fn () => new Tab();

        foreach ($tabs as $tabData) {
            if (!array_key_exists('class_name', $tabData)) {
                return false;
            }

            $tab = $tabFactory();
            $tab->active = 1;
            $tab->class_name = $tabData['class_name'] ?? '';
            $tab->icon = $tabData['icon'] ?? '';
            $tab->module = $this->module->name;
            $label = $tabData['label'] ?? 'Empty Label';

            $tab->id_parent = 0;
            if (isset($tabData['parent_class_name'])) {
                $tab->id_parent = (int) Tab::getIdFromClassName($tabData['parent_class_name']);
                $tab->route_name = $tabData['route_name'] ?? '';
            }
            foreach ($this->language->getActiveLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $label;
            }
            $tab->wording = $label;
            $tab->wording_domain = 'Modules.Alma.Admin';

            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws \PrestaShopException
     */
    public function uninstallTabs(array $tabs, callable $tabFactory = null): bool
    {
        $tabFactory = $tabFactory ?? fn () => new Tab();

        foreach ($tabs as $tabData) {
            if (!array_key_exists('class_name', $tabData)) {
                return false;
            }

            $idTab = (int) Tab::getIdFromClassName($tabData['class_name']);
            if ($idTab === 0) {
                continue;
            }

            $tab = $tabFactory();
            $tab->id = $idTab;

            if (!$tab->delete()) {
                return false;
            }
        }

        return true;
    }
}
