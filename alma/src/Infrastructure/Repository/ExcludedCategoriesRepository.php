<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;

class ExcludedCategoriesRepository
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getIds(): array
    {
        $saved = $this->configurationRepository->get(
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES
        );

        return $saved ? json_decode($saved, true) : [];
    }

    public function isWidgetDisplayNotEligibleEnabled(): bool
    {
        return (bool) $this->configurationRepository->get(
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE
        );
    }

    public function getMessage(int $idLang): string
    {
        return (string) $this->configurationRepository->get(
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . '_' . $idLang
        );
    }

    public function update(array $categoriesIds): void
    {
        $this->configurationRepository->updateValue(
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES,
            json_encode($categoriesIds)
        );
    }
}
