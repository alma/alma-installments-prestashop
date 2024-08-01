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

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ActionFrontControllerSetVariablesHookController extends FrontendHookController
{
    protected $productRepository;

    public function __construct($module, $productRepository = null)
    {
        parent::__construct($module);

        if (is_null($productRepository)) {
            $productRepository = new ProductRepository();
        }

        $this->productRepository = $productRepository;
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function run($params)
    {
        if ($this->checkIsInsuranceProduct($params)) {
            return $params['templateVars']['configuration']['is_catalog'] = true;
        }

        return false;
    }

    /**
     * @param $params
     *
     * @return bool
     */
    private function checkIsInsuranceProduct($params)
    {
        $templateVars = $params['templateVars'];
        $idInsuranceProduct = $this->productRepository->getProductIdByReference(ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE);

        if (
            is_null($idInsuranceProduct) ||
            !array_key_exists('page', $templateVars) ||
            $templateVars['page']['page_name'] !== 'product' ||
            !array_key_exists('product-id-' . $idInsuranceProduct, $templateVars['page']['body_classes'])
        ) {
            return false;
        }

        return true;
    }
}
