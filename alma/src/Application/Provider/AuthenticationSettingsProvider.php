<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\PrestaShop\Adapter\Entity\Language;

class AuthenticationSettingsProvider
{
    private \Module $module;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;
    /**
     * @var LanguageRepository
     */
    private LanguageRepository $languageRepository;

    public function __construct(
        \Module $module,
        SettingsRepository $settingsRepository,
        LanguageRepository $languageRepository,
        ToolsProxy $toolsProxy
    ) {
        $this->module = $module;
        $this->settingsRepository = $settingsRepository;
        $this->languageRepository = $languageRepository;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Get the API key from the POST if we submit Form or GET from Repository.
     * @return array
     */
    public function getApiKeys(): array
    {
        $apiKeys = $this->settingsRepository->getApiKeys();

        if ($this->toolsProxy->isSubmit('submit' . $this->module->name)) {
            foreach ($apiKeys as $mode => $apiKey) {
                if ($this->toolsProxy->getValue(ApiAdminForm::KEY_FIELDS_API_KEYS[$mode]) !== EncryptorHelper::OBSCURE_VALUE) {
                    $apiKeys[$mode] = $this->toolsProxy->getValue(ApiAdminForm::KEY_FIELDS_API_KEYS[$mode], $apiKey);
                }
            }
        }

        return $apiKeys;
    }

    /**
     * Get the environment from the POST if we submit Form or GET from Repository.
     * @return string
     */
    public function getEnvironment(): string
    {
        $environment = $this->settingsRepository->getEnvironment();

        if ($this->toolsProxy->isSubmit('submit' . $this->module->name)) {
            $environment = $this->toolsProxy->getValue(ApiAdminForm::KEY_FIELD_MODE, $environment);
        }

        return $environment;
    }

    /**
     * Get all fields with language fields exploded for each language with the language id at the end of the field name.
     * For example, if we have a field 'title' with 'lang' option and 2 languages with id 1 and 2, we will have 2 fields 'title_1' and 'title_2'.
     *
     * @param array $fields
     * @return array
     */
    public function getSplitLanguageFields(array $fields): array
    {
        $explodedFields = [];
        foreach ($fields as $name => $field) {
            if (isset($field['options']['lang']) && $field['options']['lang']) {
                foreach ($this->languageRepository->getActiveLanguages() as $language) {
                    $explodedFields[$name . '_' . $language['id_lang']] = $field;
                }
                continue;
            }

            $explodedFields[$name] = $field;
        }

        return $explodedFields;
    }

    /**
     * Get all fields from collection of forms, and return it in one array.
     * Need to externalize it for test
     *
     * @return array
     */
    public function getAllFields(): array
    {
        return FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES);
    }
}
