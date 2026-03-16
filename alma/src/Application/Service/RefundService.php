<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\RefundAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\OrderStateRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

class RefundService
{
    private \Context $context;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var OrderStateRepository
     */
    private OrderStateRepository $orderStateRepository;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        \Context $context,
        ConfigurationRepository $configurationRepository,
        OrderStateRepository $orderStateRepository,
        TranslatorInterface $translator
    ) {
        $this->context = $context;
        $this->configurationRepository = $configurationRepository;
        $this->orderStateRepository = $orderStateRepository;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function createTemplate(): string
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'alma/views/templates/admin/refund_on_change.tpl');

        return $tpl->fetch();
    }

    /**
     * Get the order states to display in the select of the refund on change state configuration.
     * @return array
     */
    public function refundStateOrder(): array
    {
        return [
            'type' => 'select',
            'label' => $this->translator->trans('Refund state order', [], 'Modules.Alma.Settings'),
            'required' => false,
            'form' => 'refund_on_change',
            'encrypted' => false,
            'options' => [
                'desc' => $this->translator->trans('Your order state to sync refund with Alma', [], 'Modules.Alma.Settings'),
                'options' => [
                    'query' => $this->orderStateRepository->getOrderStates(),
                    'id' => 'id_order_state',
                    'name' => 'name',
                ],
            ],
        ];
    }

    /**
     * On the first save we set the default value of the refund on change state configurations.
     * @return array
     */
    public function defaultFieldsToSave(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            return [];
        }

        return [
            RefundAdminForm::KEY_FIELD_REFUND_ON_CHANGE_STATE => 1,
            RefundAdminForm::KEY_FIELD_STATE_REFUND_SELECT => 7,
        ];
    }
}
