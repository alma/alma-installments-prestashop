<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use Configuration;

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
