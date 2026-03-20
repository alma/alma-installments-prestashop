<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class ExcludedCategoryProvider
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
}
