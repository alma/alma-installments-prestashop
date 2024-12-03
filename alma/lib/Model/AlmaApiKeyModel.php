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

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Exceptions\AlmaApiKeyException;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaApiKeyModel
{
    const ALMA_API_KEY_MODE = [
        'test' => ApiAdminFormBuilder::ALMA_TEST_API_KEY,
        'live' => ApiAdminFormBuilder::ALMA_LIVE_API_KEY,
    ];
    /**
     * @var ToolsProxy
     */
    private $toolsProxy;
    /**
     * @var ConfigurationProxy|mixed|null
     */
    private $configurationProxy;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel|mixed|null
     */
    private $clientModel;

    public function __construct(
        $toolsProxy = null,
        $configurationProxy = null,
        $clientModel = null
    ) {
        if (!$toolsProxy) {
            $toolsProxy = new ToolsProxy();
        }
        $this->toolsProxy = $toolsProxy;

        if (!$configurationProxy) {
            $configurationProxy = new ConfigurationProxy();
        }
        $this->configurationProxy = $configurationProxy;

        if (!$clientModel) {
            $clientModel = new ClientModel();
        }
        $this->clientModel = $clientModel;
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function checkActiveApiKeySendIsEmpty()
    {
        $mode = $this->toolsProxy->getValue(ApiAdminFormBuilder::ALMA_API_MODE);
        $apiKey = $this->toolsProxy->getValue(self::ALMA_API_KEY_MODE[$mode]);

        if (empty($apiKey)) {
            throw new AlmaApiKeyException("[Alma] API key {$mode} is empty");
        }
    }

    /**
     * @param array $apiKeys
     *
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function checkApiKeys($apiKeys)
    {
        $invalidKeys = [];
        foreach ($apiKeys as $mode => $apiKey) {
            if ($this->isObscureApiKey($apiKey)) {
                continue;
            }
            $this->clientModel->setApiKey($apiKey);
            $this->clientModel->setMode($mode);
            /**
             * @var \Alma\API\Entities\Merchant|null $merchant
             */
            $merchant = $this->clientModel->getMerchantMe();

            if (!$merchant || !$merchant->can_create_payments) {
                $invalidKeys[] = $mode;
            }
        }

        if (!empty($invalidKeys)) {
            throw new AlmaApiKeyException('[Alma] API key(s) ' . implode(', ', $invalidKeys) . ' is/are invalid');
        }
    }

    /**
     * Check if the apikey is Obscure
     *
     * @param $apiKey
     *
     * @return bool
     */
    private function isObscureApiKey($apiKey)
    {
        return $apiKey === ConstantsHelper::OBSCURE_VALUE;
    }

    /**
     * Check if the live api key is the same as the one saved
     *
     * @return bool
     */
    public function isSameLiveApiKeySaved()
    {
        $liveKey = $this->toolsProxy->getValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY);
        $savedLiveKey = SettingsHelper::getLiveKey();

        return $liveKey === $savedLiveKey && ConstantsHelper::OBSCURE_VALUE !== $liveKey;
    }

    /**
     * Check if the mode is the same as the one saved
     *
     * @return bool
     */
    public function isSameModeSaved()
    {
        $oldMode = $this->configurationProxy->get(ApiAdminFormBuilder::ALMA_API_MODE);
        $newMode = $this->toolsProxy->getValue(ApiAdminFormBuilder::ALMA_API_MODE);

        return $oldMode === $newMode;
    }

    /**
     * @throws \Exception
     */
    public function getActiveApiKey()
    {
        if (ALMA_MODE_LIVE == $this->configurationProxy->get(ApiAdminFormBuilder::ALMA_API_MODE)) {
            return SettingsHelper::getLiveKey();
        }

        return SettingsHelper::getTestKey();
    }

    /**
     * @throws \Exception
     */
    public function needApiKey()
    {
        $key = trim(SettingsHelper::getActiveAPIKey());

        return '' == $key || null == $key;
    }

    /**
     * Get all API key send from form configuration
     *
     * @return array
     */
    public function getAllApiKeySend()
    {
        return [
            'test' => trim($this->toolsProxy->getValue(ApiAdminFormBuilder::ALMA_TEST_API_KEY)),
            'live' => trim($this->toolsProxy->getValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY)),
        ];
    }
}
