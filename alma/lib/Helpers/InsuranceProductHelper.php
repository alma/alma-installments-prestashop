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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @deprecated We will remove insurance
 */
class InsuranceProductHelper
{
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @param AlmaInsuranceProductRepository $almaInsuranceProductRepository
     * @param ContextFactory $contextFactory
     */
    public function __construct($almaInsuranceProductRepository, $contextFactory)
    {
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository;
        $this->contextFactory = $contextFactory;
        $this->context = $contextFactory->getContext();
    }

    /**
     * @param int $currentCartId
     * @param int $newCartId
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     */
    public function duplicateAlmaInsuranceProducts($currentCartId, $newCartId)
    {
        $almaInsuranceProducts = $this->almaInsuranceProductRepository->getByCartIdAndShop($currentCartId, $this->context->shop->id);

        foreach ($almaInsuranceProducts as $almaInsuranceProduct) {
            $insuranceContractInfos = [
                'insurance_contract_id' => $almaInsuranceProduct['insurance_contract_id'],
                'cms_reference' => $almaInsuranceProduct['cms_reference'],
                'product_price' => $almaInsuranceProduct['product_price'],
            ];

            $this->almaInsuranceProductRepository->add(
                $newCartId,
                $almaInsuranceProduct['id_product'],
                $this->context->shop->id,
                $almaInsuranceProduct['id_product_attribute'],
                $almaInsuranceProduct['id_customization'],
                $almaInsuranceProduct['id_product_insurance'],
                $almaInsuranceProduct['id_product_attribute_insurance'],
                $almaInsuranceProduct['price'],
                $almaInsuranceProduct['id_address_delivery'],
                $insuranceContractInfos
            );
        }
    }
}
