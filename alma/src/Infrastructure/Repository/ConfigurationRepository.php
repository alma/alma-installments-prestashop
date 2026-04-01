<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use Configuration;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;

class ConfigurationRepository
{
    /**
     * @param string $key
     *
     * @return string|false
     */
    public function get(string $key): string
    {
        return Configuration::get($key);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->get(ApiAdminForm::KEY_FIELD_MODE);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID);
    }

    /**
     * @return array
     */
    public function getFeePlanList(): array
    {
        $feePlanList = $this->get(FeePlansAdminForm::KEY_FIELD_FEE_PLAN_LIST);

        if (empty($feePlanList)) {
            return [];
        }

        return json_decode($feePlanList, true);
    }

    public function getCartWidgetState(): bool
    {
        return (bool) $this->get(CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_STATE);
    }

    /**
     * @return bool
     */
    public function getCartWidgetDisplayNotEligible(): bool
    {
        return (bool) $this->get(CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE);
    }

    /**
     * @return bool
     */
    public function getCartWidgetOldPositionCustom(): bool
    {
        return (bool) $this->get(CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_CUSTOM);
    }

    /**
     * @return string
     */
    public function getCartWidgetOldPositionSelector(): string
    {
        return $this->get(CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_SELECTOR);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function updateValue(string $key, string $value): bool
    {
        return Configuration::updateValue($key, $value);
    }

    /**
     * Delete a configuration key in database (with or without language management).
     *
     * @param string $key Key to delete
     *
     * @return bool Deletion result
     */
    public function deleteByName(string $key): bool
    {
        return Configuration::deleteByName($key);
    }
}
