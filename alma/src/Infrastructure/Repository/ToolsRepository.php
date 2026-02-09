<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use Tools;

class ToolsRepository
{
    /**
     * @param string $tab
     *
     * @return bool|string
     */
    public function getAdminTokenLite(string $tab): string
    {
        return Tools::getAdminTokenLite($tab);
    }

    /**
     * @param string $key
     * @param bool|string $default_value
     *
     * @return bool|string
     */
    public function getValue(string $key, $default_value = false): string
    {
        return Tools::getValue($key, $default_value);
    }
}
