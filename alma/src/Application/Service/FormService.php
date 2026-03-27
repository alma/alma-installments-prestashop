<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\DebugAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\InPageAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\RefundAdminForm;
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
    /**
     * @var DebugAdminForm
     */
    private DebugAdminForm $debugAdminForm;
    /**
     * @var RefundAdminForm
     */
    private RefundAdminForm $refundAdminForm;
    /**
     * @var RefundService
     */
    private RefundService $refundService;
    /**
     * @var InPageAdminForm
     */
    private InPageAdminForm $inPageAdminForm;
    /**
     * @var PaymentButtonAdminForm
     */
    private PaymentButtonAdminForm $paymentButtonAdminForm;
    /**
     * @var PaymentButtonService
     */
    private PaymentButtonService $paymentButtonService;

    public function __construct(
        FeePlansService $feePlansService,
        WidgetService $widgetService,
        PaymentButtonService $paymentButtonService,
        ExcludedCategoriesService $excludedCategoriesService,
        RefundService $refundService,
        ApiAdminForm $apiAdminForm,
        FeePlansAdminForm $feePlansAdminForm,
        ProductWidgetAdminForm $productWidgetAdminForm,
        CartWidgetAdminForm $cartWidgetAdminForm,
        PaymentButtonAdminForm $paymentButtonAdminForm,
        ExcludedCategoriesAdminForm $excludedCategoriesAdminForm,
        RefundAdminForm $refundAdminForm,
        InPageAdminForm $inPageAdminForm,
        DebugAdminForm $debugAdminForm,
        ConfigurationRepository $configurationRepository
    ) {
        $this->feePlansService = $feePlansService;
        $this->widgetService = $widgetService;
        $this->paymentButtonService = $paymentButtonService;
        $this->excludedCategoriesService = $excludedCategoriesService;
        $this->refundService = $refundService;
        $this->apiAdminForm = $apiAdminForm;
        $this->feePlansAdminForm = $feePlansAdminForm;
        $this->productWidgetAdminForm = $productWidgetAdminForm;
        $this->cartWidgetAdminForm = $cartWidgetAdminForm;
        $this->paymentButtonAdminForm = $paymentButtonAdminForm;
        $this->excludedCategoriesAdminForm = $excludedCategoriesAdminForm;
        $this->refundAdminForm = $refundAdminForm;
        $this->inPageAdminForm = $inPageAdminForm;
        $this->debugAdminForm = $debugAdminForm;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Get the forms to display in the configuration page.
     * @return array
     */
    public function getForm(): array
    {
        $form = [];
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            $templateTabs = $this->feePlansService->createTemplateTabs();
            $templateWidget = $this->widgetService->createTemplate();
            $templatePaymentButton = $this->paymentButtonService->createTemplate();
            $templateExcludedCategories = $this->excludedCategoriesService->createTemplate();
            $templateRefund = $this->refundService->createTemplate();
            $form = [
                $this->feePlansAdminForm->build($templateTabs, $this->feePlansService->feePlansFields()),
                $this->productWidgetAdminForm->build($templateWidget),
                $this->cartWidgetAdminForm->build(),
                $this->paymentButtonAdminForm->build($templatePaymentButton),
                $this->excludedCategoriesAdminForm->build($templateExcludedCategories),
                $this->refundAdminForm->build($templateRefund, $this->refundService->refundStateOrder()),
                $this->inPageAdminForm->build(),
            ];
        }

        return array_merge($form, [
            $this->apiAdminForm->build(),
            $this->debugAdminForm->build(),
        ]);
    }
}
