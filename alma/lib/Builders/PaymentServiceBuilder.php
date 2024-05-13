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

namespace Alma\PrestaShop\Builders;

use Alma\PrestaShop\Services\PaymentService;
use Alma\PrestaShop\Traits\BuilderTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * PaymentServiceBuilder.
 */
class PaymentServiceBuilder
{
    use BuilderTrait;

    /**
     * @return PaymentService
     */
    public function getInstance()
    {
        return new PaymentService(
            $this->getContextFactory(),
            $this->getModuleFactory(),
            $this->getSettingsHelper(),
            $this->getLocaleHelper(),
            $this->getToolsHelper(),
            $this->getEligibilityHelper(),
            $this->getPriceHelper(),
            $this->getDateHelper(),
            $this->getCustomFieldsHelper(),
            $this->getCartData(),
            $this->getContextHelper(),
            $this->getMediaHelper(),
            $this->getPlanHelper(),
            $this->getConfigurationHelper(),
            $this->getTranslationHelper(),
            $this->getCartHelper(),
            $this->getPaymentOptionTemplateHelper(),
            $this->getPaymentOptionHelper()
        );
    }
}
