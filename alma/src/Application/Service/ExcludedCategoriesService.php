<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ExcludedCategoriesRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

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
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;
    /**
     * @var ExcludedCategoriesRepository
     */
    private ExcludedCategoriesRepository $excludedCategoriesRepository;

    public function __construct(
        \Context $context,
        ExcludedCategoriesRepository $excludedCategoriesRepository,
        ConfigurationRepository $configurationRepository,
        LanguageRepository $languageRepository,
        TranslatorInterface $translator
    ) {
        $this->context = $context;
        $this->excludedCategoriesRepository = $excludedCategoriesRepository;
        $this->configurationRepository = $configurationRepository;
        $this->languageRepository = $languageRepository;
        $this->translator = $translator;
    }

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
            $fields[ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . $suffixLanguage] = $this->translator->trans('Your cart is not eligible for payments with Alma.', [], 'Modules.Alma.Settings', $language['locale']);
        }

        return $fields;
    }

    /**
     * Add categories to the excluded categories list
     * @param array $newCategoriesIds
     */
    public function addExcludeCategories(array $newCategoriesIds): void
    {
        $categoriesIdsFromDb = $this->excludedCategoriesRepository->getIds();

        $categoriesIdsToSave = array_merge($categoriesIdsFromDb, $newCategoriesIds);
        $categoriesIdsToSave = array_unique($categoriesIdsToSave);

        $this->excludedCategoriesRepository->update($categoriesIdsToSave);
    }

    /**
     * Remove categories from the excluded categories list
     * @param array $categoryIds
     */
    public function removeExcludeCategories(array $categoryIds)
    {
        $categoriesIdsFromDb = $this->excludedCategoriesRepository->getIds();

        $updatedIds = array_values(array_diff($categoriesIdsFromDb, $categoryIds));

        $this->excludedCategoriesRepository->update($updatedIds);
    }
}
