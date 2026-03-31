<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\WidgetException;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class WidgetFrontendService
{
    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var ExcludedCategoriesService
     */
    private ExcludedCategoriesService $excludedCategoriesService;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository,
        ExcludedCategoriesService $excludedCategoriesService
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
        $this->excludedCategoriesService = $excludedCategoriesService;
    }

    /**
     * Render the widget template with the variables needed for the widget.
     * @param string $hookName
     * @return string
     */
    public function renderWidget(string $hookName): string
    {
        if (!$this->canDisplayWidgetCart($hookName) && !$this->isWidgetProductEnabled($hookName)) {
            return '';
        }

        $templatePath = _PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl';

        try {
            $tpl = $this->context->smarty->createTemplate($templatePath);
            $tpl->assign($this->getWidgetVariables($hookName));

            return $tpl->fetch();
        } catch (\SmartyException|\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the variables needed for the widget template based on the hook name and parameters.
     * @param string $hookName
     * @return array
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function getWidgetVariables(string $hookName): array
    {
        switch ($hookName) {
            case 'alma.widget.ShoppingCartFooter':
            case 'alma.widget.cart':
                /** @var \Cart $cart */
                $cart = $this->context->cart;

                if (!$cart instanceof \Cart) {
                    throw new WidgetException('Cart not found in context');
                }

                $purchaseAmount = $cart->getCartTotalPrice() ?? 0;
                $container = str_replace('.', '-', $hookName);
                $products = $cart->getProducts();
                $containerId = $this->configurationRepository->getCartWidgetOldPositionCustom()
                    ? $this->configurationRepository->getCartWidgetOldPositionSelector()
                    : '#' . $container;
                $hideIfNotEligible = (int) !$this->configurationRepository->getCartWidgetDisplayNotEligible();
                break;
            case 'alma.widget.ProductPriceBlock':
            case 'alma.widget.product':
                $controller = $this->context->controller;

                if (!method_exists($controller, 'getProduct')) {
                    throw new WidgetException('Product not found in context');
                }

                /** @var \Product $product */
                $product = $controller->getProduct();

                if (!$product instanceof \Product) {
                    throw new WidgetException('Product not found in context');
                }

                $purchaseAmount = $product->getPrice(true);
                $container = str_replace('.', '-', $hookName);
                $products = [$product];
                $containerId = '#' . $container;
                $hideIfNotEligible = (int) !$this->configurationRepository->getProductWidgetDisplayNotEligible();
                break;
            default:
                throw new WidgetException('Hook not supported for widget: ' . $hookName);
        }

        $isExcluded = $this->excludedCategoriesService->isExcluded($products);
        $showExcludedMessage = $isExcluded && $this->excludedCategoriesService->isWidgetDisplayNotEligibleEnabled();

        return [
            'container' => $container,
            'isExcluded' => $isExcluded,
            'showExcludedMessage' => $showExcludedMessage,
            'excludedMessage' => $this->excludedCategoriesService->getExcludedMessage((int) $this->context->language->id),
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => PriceHelper::priceToCent($purchaseAmount),
                'containerId' => $containerId,
                'merchantId' => $this->configurationRepository->getMerchantId(),
                'hideIfNotEligible' => $hideIfNotEligible,
                'mode' => $this->configurationRepository->getMode(),
                'plans' => $this->getActivePlans(),
                'locale' => $this->context->language->iso_code,
            ])
        ];
    }

    /**
     * Get the active fee plans from the configuration and return them in a format that can be used in the widget.
     * @return array
     */
    protected function getActivePlans(): array
    {
        $plans = [];
        $feePlanFromDb = $this->configurationRepository->getFeePlanList();

        foreach ($feePlanFromDb as $planKey => $plan) {
            if ($plan['state'] !== '1') {
                continue;
            }

            $arrayPlanKey = FeePlanHelper::getPlanFromPlanKey($planKey);
            $plans[] = [
                'installmentsCount' => $arrayPlanKey['installments_count'],
                'deferredDays' => $arrayPlanKey['deferred_days'],
                'minAmount' => (int) $plan['min_amount'],
                'maxAmount' => (int) $plan['max_amount'],
            ];
        }

        return $plans;
    }

    public function isWidgetCart(string $hookName): bool
    {
        return in_array($hookName, ['alma.widget.ShoppingCartFooter', 'alma.widget.cart']);
    }

    public function canDisplayWidgetCart(string $hookName): bool
    {
        return $this->isWidgetCart($hookName) && $this->configurationRepository->getCartWidgetState();
    }

    public function isWidgetProduct(string $hookName): bool
    {
        return in_array($hookName, ['alma.widget.ProductPriceBlock', 'alma.widget.product']);
    }

    public function isWidgetProductEnabled(string $hookName): bool
    {
        return $this->isWidgetProduct($hookName) && $this->configurationRepository->getProductWidgetState();
    }
}
