<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Application\CurlClient;
use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\Merchant;
use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;

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

    public function __construct(
        SettingsProvider $settingsProvider,
        MerchantEndpoint $merchantEndpoint
    ) {
        $this->settingsProvider = $settingsProvider;
        $this->merchantEndpoint = $merchantEndpoint;
    }

    /**
     * @throws AuthenticationException
     */
    public function getMe(): Merchant
    {
        try {
            return $this->merchantEndpoint->me();
        } catch (MerchantEndpointException $e) {
            throw new AuthenticationException($e->getMessage());
        }
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function getMerchantId(): string
    {
        try {
            return $this->merchantEndpoint->me()->getId();
        } catch (MerchantEndpointException $e) {
            throw new AuthenticationException('Impossible to get MerchantId: ' . $e->getMessage());
        }
    }

    /**
     * Check if the API key is valid by trying to retrieve the merchant information.
     */
    public function isAuthenticated(): bool
    {
        try {
            $this->merchantEndpoint->me();
        } catch (MerchantEndpointException $e) {
            // TODO: Add log the exception message
            return false;
        }

        return true;
    }

    /**
     * Check if the API keys are valid or expect Exception.
     * TODO: It will be good to send two key to the ClientConfiguration to check both keys in the PHP Client and avoid intelligence in our module
     * @return array
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function isValidKey(): array
    {
        $apiKeys = $this->settingsProvider->getApiKeys();
        $merchantIds = [];
        foreach ($apiKeys as $mode => $apiKey) {
            if (empty($apiKey)) {
                continue;
            }

            try {
                $clientConfiguration = new ClientConfiguration($apiKey, Environment::fromString($mode));
                $curlClient = new CurlClient($clientConfiguration);
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
