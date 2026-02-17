<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma\Client\Application\Endpoint\MerchantEndpoint;
use Alma\Client\Application\Exception\Endpoint\MerchantEndpointException;
use Alma\Client\Domain\Entity\Merchant;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;

class AuthenticationService
{
    /**
     * @var MerchantEndpoint
     */
    private MerchantEndpoint $merchantEndpoint;

    public function __construct(
        MerchantEndpoint $merchantEndpoint
    ) {
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
}
