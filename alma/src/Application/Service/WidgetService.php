<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class WidgetService
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
     * Create the fee plans tabs template with the fee plans list from fee plan provider to create nav tabs in the fee plans template
     * @return string
     */
    public function createTemplate(): string
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/embed_widget.tpl');

        return $tpl->fetch();
    }

    /**
     * On the first save we set the default value of the widget configurations.
     * @return array
     */
    public function defaultFieldsToSave(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            return [];
        }

        return [
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_STATE => 1,
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_STATE => 1,
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
        ];
    }
}
