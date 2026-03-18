<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class ExcludedCategoriesService
{
    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
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

        return [
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE => 'Your cart is not eligible for payments with Alma.',
        ];
    }
}
