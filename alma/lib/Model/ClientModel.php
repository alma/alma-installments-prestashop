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

use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Factories\ClientFactory;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ClientModel
{
    private static $instance;
    /**
     * @var \Alma\API\Client|null
     */
    private $almaClient;
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $mode;
    /**
     * @var \Alma\PrestaShop\Factories\ClientFactory
     */
    private $clientFactory;

    public function __construct($clientFactory = null)
    {
        if (!$clientFactory) {
            $clientFactory = new ClientFactory();
        }
        $this->clientFactory = $clientFactory;
        if (!$this->almaClient && SettingsHelper::getActiveAPIKey()) {
            $this->almaClient = $this->clientFactory->get(SettingsHelper::getActiveAPIKey(), SettingsHelper::getActiveMode());
        }
    }

    /**
     * Singleton to get the same Client instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Setter for unit test
     *
     * @param $client
     *
     * @return void
     */
    public function setClient($client)
    {
        $this->almaClient = $client;
    }

    /**
     * Setter Api Key
     *
     * @param $apiKey
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->almaClient = $this->clientFactory->create($apiKey, $this->mode);
    }

    /**
     * Setter Mode Api Key
     *
     * @param $mode
     *
     * @return void
     */
    public function setMode($mode = null)
    {
        if (!$mode) {
            $mode = SettingsHelper::getActiveMode();
        }
        $this->mode = $mode;
    }

    /**
     * Alma client can be null if no api key set.
     *
     * @return \Alma\API\Client
     *
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     */
    public function getClient()
    {
        if (!$this->almaClient) {
            throw new ClientException('No Api Key - it is normal at start');
        }

        return $this->almaClient;
    }

    /**
     * Getter Merchant Me from Alma API
     *
     * @return \Alma\API\Entities\Merchant|null
     */
    public function getMerchantMe()
    {
        try {
            return $this->getClient()->merchants->me();
        } catch (RequestError $e) {
            Logger::instance()->error('[Alma] Error getting merchant me', ['exception' => $e]);

            return null;
        } catch (ClientException $e) {
            return null;
        }
    }

    /**
     * Getter Merchant Fee Plans from Alma API
     *
     * @param $kind
     * @param $installmentsCounts
     * @param $includeDeferred
     *
     * @return \Alma\API\Entities\FeePlan[]|array
     */
    public function getMerchantFeePlans($kind = 'general', $installmentsCounts = 'all', $includeDeferred = true)
    {
        try {
            return $this->getClient()->merchants->feePlans($kind, $installmentsCounts, $includeDeferred);
        } catch (RequestError $e) {
            Logger::instance()->error('[Alma] Error getting merchant fee plans', ['exception' => $e]);

            return [];
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * Send the URL to Alma to gather CMS data
     *
     * @param string $url
     *
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     */
    public function sendUrlForGatherCmsData($url)
    {
        try {
            $this->getClient()->configuration->sendIntegrationsConfigurationsUrl($url);
        } catch (RequestException $e) {
            throw new ClientException('[Alma] Error Request for sendUrlForGatherCmsData: ' . $e->getMessage());
        } catch (RequestError $e) {
            throw new ClientException('[Alma] Error Request for sendUrlForGatherCmsData: ' . $e->getMessage());
        } catch (ClientException $e) {
            throw new ClientException('[Alma] Error to get Alma Client for sendUrlForGatherCmsData: ' . $e->getMessage());
        }
    }
}
