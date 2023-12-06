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

use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Services\InsuranceService;
use PrestaShop\PrestaShop\Core\Checkout\TermsAndConditions;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DisplayBeforeCarrierHookController extends FrontendHookController
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
     * @param $module
     */
    public function __construct($module)
    {
        $this->insuranceHelper = new InsuranceHelper();
        $this->insuranceService = new InsuranceService();
        parent::__construct($module);
    }

    /**
     * @return bool
     */
    public function canRun()
    {
         return parent::canRun()
            && $this->insuranceHelper->isInsuranceActivated()
            && $this->insuranceService->hasInsuranceInCart();
    }

    /**
     * @param $params
     * @return array
     */
    public function run($params)
    {
      return '<div class="box" id="alma-insurance" name="alma-insurance">
                      <p class="checkbox">
                            <span>
                            <input type="checkbox" name="alma-insurance-cgv" id="alma-insurance-cgv" value="0" required>
                            </span>
' . $this->module->l('By accepting to subscribe to Alma insurance, I confirm my thorough review, acceptance, and retention of the general terms outlined in the information booklet and the insurance product details. Additionally, I consent to receiving contractual information by e-mail for the purpose of securely storing it in a durable format.') . '                        
                </p>
                    </div>';

    }
}