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

use Alma\API\Exceptions\MissingKeyException;
use Alma\PrestaShop\Builders\Helpers\InsuranceHelperBuilder;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceService;
use PrestaShop\PrestaShop\Core\Checkout\TermsAndConditions;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TermsAndConditionsHookController extends FrontendHookController
{
    /** @var Alma */
    protected $module;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var InsuranceService
     */
    protected $insuranceService;
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var TermsAndConditions
     */
    protected $termsAndConditions;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        $insuranceHelperBuilder = new InsuranceHelperBuilder();
        $this->insuranceHelper = $insuranceHelperBuilder->getInstance();
        $this->insuranceService = new InsuranceService();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->termsAndConditions = new TermsAndConditions();
        parent::__construct($module);
    }

    /**
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function canRun()
    {
        return parent::canRun()
            && $this->insuranceHelper->isInsuranceActivated()
            && $this->insuranceService->hasInsuranceInCart();
    }

    /**
     * @param $params
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws MissingKeyException
     */
    public function run($params)
    {
        /**
         * @var \Cart $cart
         */
        $cart = $params['cart'];
        $insuranceContracts = $this->almaInsuranceProductRepository->getContractsInfosByIdCartAndIdShop($cart->id, $cart->id_shop);

        try {
            $termsAndConditionsInsurance = $this->insuranceService->createTextTermsAndConditions($insuranceContracts);

            $returnedTermsAndConditions[] = $this->termsAndConditions
                ->setText($termsAndConditionsInsurance['text'], $termsAndConditionsInsurance['link-notice'], $termsAndConditionsInsurance['link-ipid'], $termsAndConditionsInsurance['link-fic'])
                ->setIdentifier('terms-and-conditions-alma-insurance');

            return $returnedTermsAndConditions;
        } catch (\Exception $e) {
            Logger::instance()->warning(
                sprintf(
                    '[Alma] Warning: The contract files are missing and a client could not accept terms and conditions, message "%s", trace "%s"',
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }

        $returnedTermsAndConditions[] = $this->termsAndConditions
            ->setText($this->insuranceService->getTextTermsAndConditions())
            ->setIdentifier('terms-and-conditions-alma-insurance');

        return $returnedTermsAndConditions;
    }
}
