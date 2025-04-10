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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma;
use Alma\API\Client;
use Alma\API\Entities\Merchant;
use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Exception;

class ClientHelper
{
    public static function defaultInstance()
    {
        static $almaClient;

        if (!$almaClient) {
            $almaClient = static::createInstance(SettingsHelper::getActiveAPIKey());
        }

        return $almaClient;
    }

    /**
     * @param $apiKey
     * @param $mode
     *
     * @return Client|null
     *
     * @deprecated Use create in ClientFactory instead
     */
    public static function createInstance($apiKey, $mode = null)
    {
        if (!$mode) {
            $mode = SettingsHelper::getActiveMode();
        }

        $alma = null;

        try {
            $alma = new Client($apiKey, [
                'mode' => $mode,
                'logger' => LoggerFactory::instance(),
            ]);

            $alma->addUserAgentComponent('PrestaShop', _PS_VERSION_);
            $alma->addUserAgentComponent('Alma for PrestaShop', Alma::VERSION);
        } catch (Exception $e) {
            LoggerFactory::instance()->error('Error creating Alma API client: ' . $e->getMessage());
        }

        return $alma;
    }

    /**
     * Check Alma client
     *
     * @return Client
     *
     * @throws ClientException
     */
    public function getAlmaClient()
    {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            $msg = '[Alma] Error instantiating Alma API Client';
            LoggerFactory::instance()->error($msg);
            throw new ClientException($msg);
        }

        return $alma;
    }

    /**
     * @param null $alma
     *
     * @return Merchant
     *
     * @throws \Alma\API\RequestError
     */
    public function getMerchantsMe($alma = null)
    {
        if (!$alma) {
            $alma = ClientHelper::defaultInstance();
        }

        if (!$alma) {
            return null;
        }

        return $alma->merchants->me();
    }

    /**
     * @param array $paymentData
     *
     * @return Alma\API\Endpoints\Results\Eligibility|Alma\API\Endpoints\Results\Eligibility[]
     *
     * @throws ClientException
     * @throws RequestError
     */
    public function getPaymentEligibility($paymentData)
    {
        return $this->getAlmaClient()->payments->eligibility($paymentData);
    }

    /**
     * @param string $transactionId
     *
     * @return Alma\API\Entities\Payment
     *
     * @throws ClientException
     * @throws RequestError
     */
    public function getPaymentByTransactionId($transactionId)
    {
        return $this->getClientPaymentsEndpoint()->fetch($transactionId);
    }

    /**
     * @param string $paymentId
     * @param string $merchantOrderReference
     * @param string $status
     * @param bool|null $isShipped
     *
     * @return void
     *
     * @throws ClientException
     * @throws ParametersException
     * @throws RequestError
     * @throws RequestException
     */
    public function sendOrderStatus($paymentId, $merchantOrderReference, $status, $isShipped = null)
    {
        $this->getClientPaymentsEndpoint()->addOrderStatusByMerchantOrderReference($paymentId, $merchantOrderReference, $status, $isShipped);
    }

    /**
     * @return Alma\API\Endpoints\Orders
     *
     * @throws ClientException
     */
    public function getClientOrdersEndpoint()
    {
        return $this->getAlmaClient()->orders;
    }

    /**
     * @return Alma\API\Endpoints\Payments
     *
     * @throws ClientException
     */
    public function getClientPaymentsEndpoint()
    {
        return $this->getAlmaClient()->payments;
    }

    /**
     * @param string $url
     *
     * @throws \Alma\PrestaShop\Exceptions\ClientException
     */
    public function sendUrlForGatherCmsData($url)
    {
        try {
            $this->getAlmaClient()->configuration->sendIntegrationsConfigurationsUrl($url);
        } catch (Alma\API\Exceptions\RequestException $e) {
            throw new ClientException('[Alma] Error Request: ' . $e->getMessage());
        } catch (RequestError $e) {
            throw new ClientException('[Alma] Error Request: ' . $e->getMessage());
        } catch (ClientException $e) {
            throw new ClientException('[Alma] Error to get Alma Client: ' . $e->getMessage());
        }
    }
}
