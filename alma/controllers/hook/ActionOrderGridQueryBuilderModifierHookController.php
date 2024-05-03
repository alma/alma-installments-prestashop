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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Exceptions\InsuranceInstallException;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityManager\QueryBuilder;
use PrestaShop\PrestaShop\Core\Search\Filters\OrderFilters;

class ActionOrderGridQueryBuilderModifierHookController extends FrontendHookController
{
    /**
     * @var InsuranceHelper $insuranceHelper
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

        $this->insuranceHelper = new InsuranceHelper();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     *
     * @throws InsuranceInstallException
     */
    public function run($params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var OrderFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];

        $searchQueryBuilder->addSelect(
            'IF(aip.`id_order` IS NOT NULL,1,0) AS `has_alma_insurance`'
        );

        $searchQueryBuilder->leftJoin(
            'o',
            '`' . _DB_PREFIX_ . 'alma_insurance_product`',
            'aip',
            'aip.`id_order` = o.`id_order`'
        );

        $searchQueryBuilder->groupBy('o.`id_order`');

        if ('has_alma_insurance' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('aip.`id_order`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('has_alma_insurance' === $filterName) {
                $searchQueryBuilder->andWhere('aip.`id_order` IS ' . ($filterValue ? 'NOT' : '') . ' NULL');
            }
        }
    }
}
