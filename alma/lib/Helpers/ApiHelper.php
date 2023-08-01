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
namespace Alma\PrestaShop\Helpers;

use Alma\API\Entities\Merchant;
use Alma\PrestaShop\Exceptions\ActivationException;
use Alma\PrestaShop\Exceptions\ApiMerchantsException;
use Alma\PrestaShop\Exceptions\WrongCredentialsException;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;

class ApiHelper
{
    /**
     * @return Merchant|null
     *
     * @throws ActivationException
     * @throws ApiMerchantsException
     * @throws WrongCredentialsException
     */
    public static function getMerchant($module, $alma = null)
    {
        if (!$alma) {
            $alma = ClientHelper::defaultInstance();
        }

        if (!$alma) {
            return null;
        }

        try {
            /**
             * @var \Alma\API\Entities\Merchant $merchant
             */
            $merchant = $alma->merchants->me();
        } catch (\Exception $e) {
            if ($e->response && 401 === $e->response->responseCode) {
                throw new WrongCredentialsException($module);
            }

            throw new ApiMerchantsException($module->l('Alma encountered an error when fetching merchant status, please check your api keys or retry later.', 'GetContentHookController'), $e->getCode(), $e);
        }

        if (!$merchant->can_create_payments) {
            throw new ActivationException($module);
        }

        static::saveFeatureFlag($merchant, 'cms_allow_inpage', ConstantsHelper::ALMA_ALLOW_INPAGE);

        return $merchant;
    }

    /**
     * @param \Alma\API\Entities\Merchant $merchant
     * @param string $merchantKey
     * @param string $configKey
     *
     * @return void
     */
    protected static function saveFeatureFlag($merchant, $merchantKey, $configKey)
    {
        $value = 1;

        if (property_exists($merchant, $merchantKey)) {
            $value = (int) $merchant->$merchantKey;
        }

        SettingsHelper::updateValue($configKey, $value);

        // If Inpage not allowed we ensure that inpage is deactivated in database
        if (0 === $value) {
            SettingsHelper::updateValue(InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE, $value);
        }
    }
}
