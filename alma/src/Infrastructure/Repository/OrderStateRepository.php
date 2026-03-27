<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

class OrderStateRepository
{
    private \Context $context;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get all available order statuses.
     *
     * @return array Order statuses
     */
    public function getOrderStates(): array
    {
        return \OrderState::getOrderStates($this->context->language->id);
    }
}
