<?php

namespace PrestaShop\Module\Alma\Application\Service;

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

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Render the widget template with the variables needed for the widget.
     * @param string $hookName
     * @param array $configuration
     * @return string
     */
    public function renderWidget(string $hookName, array $configuration): string
    {
        $templatePath = '';
        if (in_array($hookName, ['alma.widget.ShoppingCartFooter', 'alma.widget.cart'])) {
            $templatePath = _PS_MODULE_DIR_ . 'alma/views/templates/widget/cart.tpl';
        }

        try {
            $tpl = $this->context->smarty->createTemplate($templatePath);
            $tpl->assign($this->getWidgetVariables($hookName, $configuration));

            return $tpl->fetch();
        } catch (\SmartyException|\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getWidgetVariables($hookName, array $configuration): array
    {
        switch ($hookName) {
            case 'alma.widget.ShoppingCartFooter':
                /** @var \Cart $cart */
                $cart = $configuration['cart'];
                $purchaseAmount = $cart->getCartTotalPrice();
                $containerId = '#alma-widget-cart';
                break;
            case 'alma.widget.cart':
                $cart = $configuration['cart'];
                $purchaseAmount = $cart['totals']['total']['amount'];
                $containerId = '#alma-widget-cart';
                break;
            default:
                return ['error_widget' => true];
        }

        return [
            'purchaseAmount' => PriceHelper::priceToCent($purchaseAmount),
            'containerId' => $containerId,
            'merchantId' => $this->configurationRepository->getMerchantId(),
            'hideIfNotEligible' => !$this->configurationRepository->getCartWidgetDisplayNotEligible(),
            'mode' => $this->configurationRepository->getMode(),
            'plans' => json_encode($this->getActivePlans()),
            'locale' => $this->context->language->iso_code,
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

            $plans[] = [
                'installmentsCount' => FeePlanHelper::getPlanFromPlanKey($planKey)['installments_count'],
                'deferredDays' => FeePlanHelper::getPlanFromPlanKey($planKey)['deferred_days'],
                'minAmount' => (int) $plan['min_amount'],
                'maxAmount' => (int) $plan['max_amount'],
            ];
        }

        return $plans;
    }
}
