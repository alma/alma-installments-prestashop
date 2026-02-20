<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Application\CurlClient;
use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\Merchant;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Factory\CurlClientFactory;

class AuthenticationService
{
    /**
     * @var MerchantEndpoint
     */
    private MerchantEndpoint $merchantEndpoint;
    /**
     * @var \PrestaShop\Module\Alma\Application\Provider\SettingsProvider
     */
    private SettingsProvider $settingsProvider;
    /**
     * @var CurlClientFactory
     */
    private CurlClientFactory $curlClientFactory;

    public function __construct(
        SettingsProvider $settingsProvider,
        MerchantEndpoint $merchantEndpoint,
        CurlClientFactory $curlClientFactory
    ) {
        $this->settingsProvider = $settingsProvider;
        $this->merchantEndpoint = $merchantEndpoint;
        $this->curlClientFactory = $curlClientFactory;
    }

    /**
     * Check if the API keys are valid or expect Exception.
     * TODO: It will be good to send two key to the ClientConfiguration to check both keys in the PHP Client and avoid intelligence in our module
     * TODO : I can't test the validation key, we need to add setter for curlClient on merchantEndpoint.
     * @return array
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function isValidKeys(): array
    {
        $apiKeys = $this->settingsProvider->getApiKeys();
        $merchantIds = [];
        foreach ($apiKeys as $mode => $apiKey) {
            if (empty($apiKey)) {
                continue;
            }

            try {
                $curlClient = $this->curlClientFactory->create($apiKey, $mode);
                $merchantEndpoint = new MerchantEndpoint($curlClient);
                $merchantIds[$mode] = $merchantEndpoint->me()->getId();
            } catch (MerchantEndpointException $e) {
                throw new AuthenticationException($e->getMessage() . ': ' . $mode);
            }
        }

        return $merchantIds;
    }

    /**
     * Check if the merchant IDs are the same between environments or throw Exception.
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function checkSameMerchantIds(array $merchantIds)
    {
        if (count(array_unique($merchantIds)) !== 1) {
            throw new AuthenticationException('Merchant IDs are different between environments, please check your API keys.');
        }
    }
}
