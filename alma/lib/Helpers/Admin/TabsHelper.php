<?php
/**
 * 2018-2023 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers\Admin;

use Alma\PrestaShop\Helpers\ConstantsHelper;
use PrestaShop\PrestaShop\Adapter\Entity\Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TabsHelper
{
    /**
     * Add Alma in backoffice menu.
     *
     * @param $moduleName
     * @param string $class class controller
     * @param string $name tab title
     * @param null $parent parent class name
     * @param null $position order in menu
     * @param null $icon fontAwesome class icon
     *
     * @return bool if save successfully
     */
    public function installTab($moduleName, $class, $name, $parent = null, $position = null, $icon = null)
    {
        $tab = $this->getInstanceFromClassName($class);
        $tab->active = false !== $name;
        $tab->class_name = $class;
        $tab->name = [];

        if ($position) {
            $tab->position = $position;
        }

        foreach (\Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->id_parent = 0;
        if ($parent) {
            if (version_compare(_PS_VERSION_, '1.7', '>=') && $icon) {
                $tab->icon = $icon;
            }

            $parentTab = $this->getInstanceFromClassName($parent);
            $tab->id_parent = $parentTab->id;
        }

        $tab->module = $moduleName;

        return $tab->save();
    }

    /**
     * @param $tabs
     *
     * @return bool
     */
    public function installTabs($tabs)
    {
        $allTableAreActivated = true;

        foreach ($tabs as $class => $dataTab) {
            if (!$this->installTab(
                ConstantsHelper::ALMA_MODULE_NAME,
                $class,
                $dataTab['name'],
                $dataTab['parent'],
                $dataTab['position'],
                $dataTab['icon']
            )) {
                $allTableAreActivated = false;
            }
        }

        return $allTableAreActivated;
    }

    /**
     * @params string $class
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function uninstallTab($class)
    {
        $tab = $this->getInstanceFromClassName($class);
        if (!\Validate::isLoadedObject($tab)) {
            return true;
        }

        return $tab->delete();
    }

    /**
     * @param $tabs
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function uninstallTabs($tabs)
    {
        $allTableAreActivated = true;

        foreach ($tabs as $class => $dataTab) {
            if (!$this->uninstallTab($class)) {
                $allTableAreActivated = false;
            }
        }

        return $allTableAreActivated;
    }

    /**
     * @param string $className
     *
     * @return \Tab
     */
    public function getInstanceFromClassName($className)
    {
        /*
         * @var Tab|object $tab
         */
        return \Tab::getInstanceFromClassName($className);
    }
}
