<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

class WidgetService
{
    const ALLOWED_CONTROLLERS = [
        'ProductController' => 'product',
        'CartController' => 'cart',
        'IndexController' => 'index',
    ];

    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository,
        TranslatorInterface $translator
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
        $this->translator = $translator;
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

    /**
     * Get the old widget position field to migrate the configuration from our module v5.
     * @return array
     */
    public function getOldCartWidgetPositionForm(): array
    {
        if (!$this->configurationRepository->getCartWidgetOldPositionCustom()) {
            return [];
        }

        return [
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_CUSTOM => [
                'type' => 'switch',
                'label' => $this->translator->trans('Old Custom position', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'cart_widget',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => $this->translator->trans('Enabled', [], 'Modules.Alma.Settings'),
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => $this->translator->trans('Disabled', [], 'Modules.Alma.Settings')
                        ]
                    ],
                    'desc' => $this->translator->trans('Used for disabled old custom position of widget', [], 'Modules.Alma.Settings'),
                ],
            ]
        ];
    }

    /**
     * Get the old product widget position field to migrate the configuration from our module v5.
     * @return array
     */
    public function getOldProductWidgetPositionForm(): array
    {
        if (!$this->configurationRepository->getProductWidgetOldPositionCustom()) {
            return [];
        }

        return [
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_POSITION_CUSTOM => [
                'type' => 'switch',
                'label' => $this->translator->trans('Old Custom position', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'product_widget',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => $this->translator->trans('Enabled', [], 'Modules.Alma.Settings'),
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => $this->translator->trans('Disabled', [], 'Modules.Alma.Settings')
                        ]
                    ],
                    'desc' => $this->translator->trans('Used for disabled old custom position of widget', [], 'Modules.Alma.Settings'),
                ],
            ]
        ];
    }

    /**
     * Get the value of the old widget position field to migrate the configuration from our module v5.
     * @return array
     */
    public function fieldsValueOldWidgetPosition(): array
    {
        $fieldsValue = [];

        if (!empty($this->configurationRepository->getCartWidgetOldPositionCustom())) {
            $fieldsValue[CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_CUSTOM] = 1;
        }

        if (!empty($this->configurationRepository->getProductWidgetOldPositionCustom())) {
            $fieldsValue[ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_POSITION_CUSTOM] = 1;
        }

        return $fieldsValue;
    }
}
