<?php

namespace PrestaShop\Module\Alma\Infrastructure\Proxy;

use Tools;

class ToolsProxy
{
    /**
     * Get a token for a tab.
     * @param string $tab
     *
     * @return bool|string
     */
    public function getAdminTokenLite(string $tab): string
    {
        return Tools::getAdminTokenLite($tab);
    }

    /**
     * Get a value from $_POST / $_GET
     *  if unavailable, take a default value.
     * @param string $key
     * @param bool|string $default_value
     *
     * @return bool|string
     */
    public function getValue(string $key, $default_value = false): string
    {
        return Tools::getValue($key, $default_value);
    }

    /**
     * Check if submit has been posted.
     *
     * @param string $submit submit name
     */
    public function isSubmit(string $submit): bool
    {
        return Tools::isSubmit($submit);
    }
}
