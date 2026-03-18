<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

class ExcludedCategoriesService
{
    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var LanguageRepository
     */
    private LanguageRepository $languageRepository;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository,
        LanguageRepository $languageRepository
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
        $this->languageRepository = $languageRepository;
    }

    // TODO : Provisional values, need to be get the transalation value from .xlf file when I18N rebased
    private const VALUE_EXCLUDED_CATEGORIES_MESSAGE = [
        'en' => 'Your cart is not eligible for payments with Alma.',
        'fr' => 'Paiements avec Alma indisponibles'
    ];

    /**
     * @return string
     */
    public function createTemplate(): string
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/excluded_categories.tpl');

        $tpl->assign([
            'excludedCategoriesPageLink' => '/link_of_the_categories_page_in_prestashop_backoffice',
            'excludedCategories' => 'Add Here the excluded categories list',
        ]);

        return $tpl->fetch();
    }

    /**
     * On the first save we set the default value of the excluded categories configurations.
     * @return array
     */
    public function defaultFieldsToSave(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            return [];
        }

        $fields = [
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
        ];

        foreach ($this->languageRepository->getActiveLanguages() as $language) {
            $suffixLanguage = '_' . $language['id_lang'];
            $fields[ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . $suffixLanguage] = self::VALUE_EXCLUDED_CATEGORIES_MESSAGE[$language['iso_code']];
        }

        return $fields;
    }
}
