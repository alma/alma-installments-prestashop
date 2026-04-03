<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\WidgetException;
use PrestaShop\Module\Alma\Application\Helper\FeePlanHelper;
use PrestaShop\Module\Alma\Application\Helper\PriceHelper;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class WidgetFrontendService
{
    const WIDGET_HOOK_SHOPPING_CART_FOOTER = 'alma.widget.ShoppingCartFooter';
    const WIDGET_CART = 'alma.widget.cart';
    const WIDGET_HOOK_PRODUCT_PRICE_BLOCK = 'alma.widget.ProductPriceBlock';
    const WIDGET_PRODUCT = 'alma.widget.product';
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
        if (!$this->canDisplayWidgetCart($hookName) && !$this->canDisplayWidgetProduct($hookName)) {
            return '';
        }

        $templatePath = _PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl';

        try {
            $tpl = $this->context->smarty->createTemplate($templatePath);
            $tpl->assign($this->getWidgetVariables($hookName));

            return $tpl->fetch();
        } catch (WidgetException|\SmartyException|\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the variables needed for the widget template based on the hook name and parameters.
     * @param string $hookName
     * @return array
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    private function getWidgetVariables(string $hookName): array
    {
        switch ($hookName) {
            case self::WIDGET_HOOK_SHOPPING_CART_FOOTER:
            case self::WIDGET_CART:
                /** @var \Cart $cart */
                $cart = $this->context->cart;

                if (!$cart instanceof \Cart) {
                    throw new WidgetException('Cart not found in context');
                }

                $purchaseAmount = $cart->getCartTotalPrice() ?? 0;
                $container = str_replace('.', '-', $hookName);
                $products = $cart->getProducts();
                $productEmbeddedAttributes = [];
                $containerId = $this->configurationRepository->getCartWidgetOldPositionCustom()
                    ? $this->configurationRepository->getCartWidgetOldPositionSelector()
                    : '#' . $container;
                $hideIfNotEligible = (int) !$this->configurationRepository->getCartWidgetDisplayNotEligible();
                break;
            case self::WIDGET_HOOK_PRODUCT_PRICE_BLOCK:
            case self::WIDGET_PRODUCT:
            /** @var \ProductControllerCore $controller */
                $controller = $this->context->controller;

                if (!method_exists($controller, 'getProduct')) {
                    throw new WidgetException('getProduct does not exist in context controller');
                }

                /** @var \Product $product */
                $product = $controller->getProduct();

                if (!$product instanceof \Product) {
                    throw new WidgetException('Product not found in context controller');
                }

                $purchaseAmount = $product->getPrice();
                $container = str_replace('.', '-', $hookName);
                /** @var \Product $products */
                $products = [$product];
                $productEmbeddedAttributes = $controller->getTemplateVarProduct()->getEmbeddedAttributes();
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
            'productEmbeddedAttributes' => json_encode($productEmbeddedAttributes),
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

    /**
     * Check if the hook name is for the cart widget.
     * @param string $hookName
     * @return bool
     */
    private function isWidgetCart(string $hookName): bool
    {
        return in_array($hookName, [self::WIDGET_HOOK_SHOPPING_CART_FOOTER, self::WIDGET_CART]);
    }

    /**
     * Check if the cart widget can be displayed based on the configuration and the hook name.
     * @param string $hookName
     * @return bool
     */
    private function canDisplayWidgetCart(string $hookName): bool
    {
        return $this->isWidgetCart($hookName) && $this->configurationRepository->getCartWidgetState();
    }

    /**
     * Check if the hook name is for the product widget.
     * @param string $hookName
     * @return bool
     */
    private function isWidgetProduct(string $hookName): bool
    {
        return in_array($hookName, [self::WIDGET_HOOK_PRODUCT_PRICE_BLOCK, self::WIDGET_PRODUCT]);
    }

    /**
     * Check if the product widget can be displayed based on the configuration and the hook name.
     * @param string $hookName
     * @return bool
     */
    private function canDisplayWidgetProduct(string $hookName): bool
    {
        return $this->isWidgetProduct($hookName) && $this->configurationRepository->getProductWidgetState();
    }
}
