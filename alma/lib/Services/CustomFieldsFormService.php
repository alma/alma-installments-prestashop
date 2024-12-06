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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Exceptions\MissingParameterException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomFieldsFormService
{
    /**
     * @var \Context
     */
    private $context;
    /**
     * @var ToolsProxy
     */
    private $toolsProxy;
    /**
     * @var ConfigurationProxy
     */
    private $configurationProxy;

    /**
     * @param $context
     * @param $toolsProxy
     * @param $configurationProxy
     */
    public function __construct(
        $context = null,
        $toolsProxy = null,
        $configurationProxy = null
    ) {
        if (!$context) {
            $context = (new ContextFactory())->getContext();
        }
        $this->context = $context;
        if (!$toolsProxy) {
            $toolsProxy = new ToolsProxy();
        }
        $this->toolsProxy = $toolsProxy;
        if (!$configurationProxy) {
            $configurationProxy = new ConfigurationProxy();
        }
        $this->configurationProxy = $configurationProxy;
    }

    /**
     * Save the Custom Fields
     * This is the fields for text payment button with multiple language
     *
     * @throws \Alma\PrestaShop\Exceptions\MissingParameterException
     */
    public function save()
    {
        // Get languages are active
        $languages = $this->context->controller->getLanguages();

        $titlesPayNow = $titles = $titlesDeferred = $titlesCredit = $descriptionsPayNow = $descriptions = $descriptionsDeferred = $descriptionsCredit = $nonEligibleCategoriesMsg = [];

        foreach ($languages as $language) {
            $locale = $language['iso_code'];
            $languageId = $language['id_lang'];

            // E.g.: iso_code = 'en',  locale = 'en-US'
            // Since PS 1.7, the locale is not available in the language array
            if (array_key_exists('locale', $language)) {
                $locale = $language['locale'];
            }

            $titlesPayNow[$languageId] = $this->getLocaleAndString(
                $languageId,
                $locale,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE
            );
            $titles[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE);
            $titlesDeferred[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE);
            $titlesCredit[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE);
            $descriptionsPayNow[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC);
            $descriptions[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC);
            $descriptionsDeferred[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC);
            $descriptionsCredit[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC);
            $nonEligibleCategoriesMsg[$languageId] = $this->getLocaleAndString($languageId, $locale, ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES);
        }

        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE, json_encode($titles));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE, json_encode($titlesDeferred));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE, json_encode($titlesCredit));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE, json_encode($titlesPayNow));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC, json_encode($descriptions));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC, json_encode($descriptionsDeferred));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC, json_encode($descriptionsCredit));
        $this->configurationProxy->updateValue(PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC, json_encode($descriptionsPayNow));
        $this->configurationProxy->updateValue(ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES, json_encode($nonEligibleCategoriesMsg));
    }

    /**
     * Get array with locale and string about key form and language id
     * This array allow to identify the wording to put in the right language field in the form
     *
     * @param int $languageId
     * @param string $locale
     * @param string $keyForm
     *
     * @return array
     *
     * @throws MissingParameterException
     */
    protected function getLocaleAndString($languageId, $locale, $keyForm)
    {
        $result = [
            'locale' => $locale,
            'string' => $this->toolsProxy->getValue(sprintf('%s_%s', $keyForm, $languageId)),
        ];

        if (empty($result['string'])) {
            throw new MissingParameterException($locale, $keyForm, $languageId);
        }

        return $result;
    }
}
