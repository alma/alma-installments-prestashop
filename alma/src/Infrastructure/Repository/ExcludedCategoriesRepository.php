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

    public function update(array $categoriesIds): void
    {
        $this->configurationRepository->updateValue(
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES,
            json_encode($categoriesIds)
        );
    }
}
