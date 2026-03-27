<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;

class EnvironmentFactory
{
    /**
     * @var AuthenticationSettingsProvider
     */
    private AuthenticationSettingsProvider $authenticationSettingsProvider;

    public function __construct(AuthenticationSettingsProvider $authenticationSettingsProvider)
    {
        $this->authenticationSettingsProvider = $authenticationSettingsProvider;
    }

    /**
     * Create an Environment instance using the mode from the settings repository or from the POST of Form if submitted.
     * The provider get the right value
     *
     * @return Environment
     */
    public function create(): Environment
    {
        $environment = $this->authenticationSettingsProvider->getEnvironment();

        return new Environment($environment);
    }
}
