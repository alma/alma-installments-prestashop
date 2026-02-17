<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class ClientConfigurationFactory
{
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var Environment
     */
    private Environment $environment;
    private \Module $module;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function __construct(
        SettingsRepository $settingsRepository,
        Environment $environment,
        \Module $module,
        ToolsProxy $toolsProxy
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->environment = $environment;
        $this->module = $module;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Create a ClientConfiguration instance using the API key from the settings repository or from the POST of Form if submitted.
     * @return ClientConfiguration
     */
    public function create(): ClientConfiguration
    {
        $apiKey = $this->settingsRepository->getApiKey();

        // TODO : Improve it
        if (
            $this->toolsProxy->isSubmit('submit' . $this->module->name)
            && $this->toolsProxy->getValue('ALMA_TEST_API_KEY') !== EncryptionHelper::OBSCURE_VALUE
        ) {
            $apiKey = $this->toolsProxy->getValue('ALMA_TEST_API_KEY', $apiKey);
        }

        return new ClientConfiguration($apiKey, $this->environment);
    }
}
