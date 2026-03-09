<?php

namespace PrestaShop\Module\Alma\Infrastructure\Proxy;

use Tools;

/**
 * Class ToolsProxy
 *
 * This class serves as a proxy to the Tools class, allowing for easier testing and decoupling from the static methods of Tools.
 * TODO: If to much complexity will be set in this class, we will be able to split it into multiple Helpers with specific purposes.
 */
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
     * If unavailable, take a default value.
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
