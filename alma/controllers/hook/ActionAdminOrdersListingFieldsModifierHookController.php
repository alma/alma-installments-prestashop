<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Controllers\Hook;

use Alma\PrestaShop\Builders\Helpers\InsuranceHelperBuilder;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use PrestaShop\PrestaShop\Adapter\Entity\ModuleAdminController;

class ActionAdminOrdersListingFieldsModifierHookController extends ModuleAdminController
{
    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    public function canRun()
    {
        // Front controllers can run if the module is properly configured ...
        return SettingsHelper::isFullyConfigured()
            // ... and the plugin is in LIVE mode, or the visitor is an admin
            && $this->insuranceHelper->isInsuranceActivated();
    }

    /**
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     */
    public function run($params)
    {
        if (isset($params['select'])) {
            $params['select'] .= ' ,IF(aip.`id_order` IS NOT NULL,1,0) as has_alma_insurance ';
        }
        if (isset($params['join'])) {
            $params['join'] .= 'LEFT JOIN ' . _DB_PREFIX_ . 'alma_insurance_product aip ON (a.id_order = aip.id_order )';
        }
        if (isset($params['group_by'])) {
            $params['group_by'] .= 'aip.id_order';
        }
        $params['fields']['has_alma_insurance'] = [
            'title' => 'Has Insurance',
            'type' => 'select',
            'list' => [
                0 => 'No',
                1 => 'Yes',
            ],
            'filter_key' => 'aip!id_order',
        ];
    }
}
