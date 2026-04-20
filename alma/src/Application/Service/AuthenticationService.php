<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Infrastructure\Factory\CurlClientFactory;

class AuthenticationService
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider
     */
    private AuthenticationSettingsProvider $authenticationSettingsProvider;
    /**
     * @var CurlClientFactory
     */
    private CurlClientFactory $curlClientFactory;

    public function __construct(
        AuthenticationSettingsProvider $authenticationSettingsProvider,
        CurlClientFactory $curlClientFactory
    ) {
        $this->authenticationSettingsProvider = $authenticationSettingsProvider;
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
        $apiKeys = $this->authenticationSettingsProvider->getApiKeys();
        $merchantIds = [];
        foreach ($apiKeys as $mode => $apiKey) {
            if (empty($apiKey)) {
                continue;
            }

            $merchantIds[$mode] = $this->checkAuthentication($apiKey, $mode);
        }

        return $merchantIds;
    }

    /**
     * Check if the API key is valid or expect Exception.
     * @param string $apiKey
     * @param string $mode
     * @return string
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function checkAuthentication(string $apiKey, string $mode): string
    {
        try {
            $curlClient = $this->curlClientFactory->create($apiKey, $mode);
            $merchantEndpoint = new MerchantEndpoint($curlClient);
            return $merchantEndpoint->me()->getId();
        } catch (MerchantEndpointException $e) {
            throw new AuthenticationException($e->getMessage() . ': ' . $mode);
        }
    }

    /**
     * Check if the merchant IDs are the same between environments or throw Exception.
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function checkSameMerchantIds(array $merchantIds)
    {
        if (count(array_unique($merchantIds)) !== 1) {
            throw new AuthenticationException('The API keys you entered belong to two different merchant accounts. Please use keys from the same account.');
        }
    }
}
