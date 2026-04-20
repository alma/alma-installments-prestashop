<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ProductProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ExcludedCategoriesRepository;
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
    /**
     * @var ExcludedCategoriesRepository
     */
    private ExcludedCategoriesRepository $excludedCategoriesRepository;
    /**
     * @var ProductProxy
     */
    private ProductProxy $productProxy;
    private Alma $module;

    public function __construct(
        Alma $module,
        \Context $context,
        ExcludedCategoriesRepository $excludedCategoriesRepository,
        ConfigurationRepository $configurationRepository,
        LanguageRepository $languageRepository,
        ProductProxy $productProxy
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->excludedCategoriesRepository = $excludedCategoriesRepository;
        $this->configurationRepository = $configurationRepository;
        $this->languageRepository = $languageRepository;
        $this->productProxy = $productProxy;
    }

    /**
     * @return string
     * @throws \SmartyException
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
            $fields[ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . $suffixLanguage] = $this->module->translate('Your cart is not eligible for payments with Alma.', [], 'Modules.Alma.Settings', $language['locale']);
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

    /**
     * Check if any product in the given list belongs to an excluded category.
     * @param array $products Array of products as returned by Cart::getProducts()
     * @return bool
     */
    public function isExcluded(array $products): bool
    {
        $excludedIds = $this->excludedCategoriesRepository->getIds();

        if (empty($excludedIds)) {
            return false;
        }

        foreach ($products as $productData) {
            $productCategories = $this->productProxy->getCategories((int) $productData['id_product']);
            if (!empty(array_intersect($productCategories, $excludedIds))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isWidgetDisplayNotEligibleEnabled(): bool
    {
        return $this->excludedCategoriesRepository->isWidgetDisplayNotEligibleEnabled();
    }

    /**
     * @param int $idLang
     * @return string
     */
    public function getExcludedMessage(int $idLang): string
    {
        return $this->excludedCategoriesRepository->getMessage($idLang);
    }
}
