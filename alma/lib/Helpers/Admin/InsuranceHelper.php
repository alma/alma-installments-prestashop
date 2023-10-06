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

class InsuranceHelper
{
    /**
     * @var array
     *
     */
    protected static $tabInsuranceDescription = [
        'position' => 3,
        'icon' => 'not_interested',
    ];

    /**
     * @var TabsHelper
     */
    private $tabsHelper;

    public function __construct()
    {
        $this->tabsHelper = new TabsHelper();
    }

    /**
     * @param int $isAllowInsurance
     * @return bool|null
     * @throws \PrestaShopException
     */
    public function handleBOMenu($module, $isAllowInsurance) {
        /**
         * @var Tab|object $tab
         */
        $tab = \Tab::getInstanceFromClassName(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);

        // Remove tab if the tab exists and we are not allowed to have it
        if (
            $tab->id
            && !$isAllowInsurance
        ) {
            return $this->tabsHelper->uninstallTab(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);
        }

        // Add tab if the tab not exists and we are allowed to have it
        if (
            !$tab->id
            && $isAllowInsurance
        ) {
            return $this->tabsHelper->installTab(
                ConstantsHelper::ALMA_MODULE_NAME,
                ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                $module->l('Insurance'),
                ConstantsHelper::ALMA_MODULE_NAME,
                static::$tabInsuranceDescription['position'],
                static::$tabInsuranceDescription['icon']
            );
        }

        return null;
    }
}