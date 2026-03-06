<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FormService
{
    /**
     * @var ApiAdminForm
     */
    private ApiAdminForm $apiAdminForm;
    /**
     * @var FeePlansAdminForm
     */
    private FeePlansAdminForm $feePlansAdminForm;
    /**
     * @var FeePlansService
     */
    private FeePlansService $feePlansService;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var ProductWidgetAdminForm
     */
    private ProductWidgetAdminForm $productWidgetAdminForm;
    /**
     * @var CartWidgetAdminForm
     */
    private CartWidgetAdminForm $cartWidgetAdminForm;
    /**
     * @var WidgetService
     */
    private WidgetService $widgetService;
    /**
     * @var ExcludedCategoriesService
     */
    private ExcludedCategoriesService $excludedCategoriesService;
    /**
     * @var ExcludedCategoriesAdminForm
     */
    private ExcludedCategoriesAdminForm $excludedCategoriesAdminForm;

    public function __construct(
        FeePlansService $feePlansService,
        WidgetService $widgetService,
        ExcludedCategoriesService $excludedCategoriesService,
        ApiAdminForm $apiAdminForm,
        FeePlansAdminForm $feePlansAdminForm,
        ProductWidgetAdminForm $productWidgetAdminForm,
        CartWidgetAdminForm $cartWidgetAdminForm,
        ExcludedCategoriesAdminForm $excludedCategoriesAdminForm,
        ConfigurationRepository $configurationRepository
    ) {
        $this->feePlansService = $feePlansService;
        $this->widgetService = $widgetService;
        $this->excludedCategoriesService = $excludedCategoriesService;
        $this->apiAdminForm = $apiAdminForm;
        $this->feePlansAdminForm = $feePlansAdminForm;
        $this->productWidgetAdminForm = $productWidgetAdminForm;
        $this->cartWidgetAdminForm = $cartWidgetAdminForm;
        $this->excludedCategoriesAdminForm = $excludedCategoriesAdminForm;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Get the forms to display in the configuration page.
     * @return array
     */
    public function getForm(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            $templateTabs = $this->feePlansService->createTemplateTabs();
            $templateWidget = $this->widgetService->createTemplate();
            $templateExcludedCategories = $this->excludedCategoriesService->createTemplate();
            $form = [
                $this->feePlansAdminForm->build($templateTabs, $this->feePlansService->feePlansFields()),
                $this->productWidgetAdminForm->build($templateWidget),
                $this->cartWidgetAdminForm->build(),
                $this->excludedCategoriesAdminForm->build($templateExcludedCategories),
            ];
        }

        $form[] = $this->apiAdminForm->build();

        return $form;
    }
}
