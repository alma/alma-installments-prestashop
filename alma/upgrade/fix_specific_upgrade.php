<?php

// Some versions are not compatible with upgrade (1.4.3, 1.4.4, 2.0.0) #ECOM-2340

function fixSpecificUpgrade($module)
{
    // Retrieve the current version of the module before migration
    $sql = 'SELECT version FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL($module->name) . '"';
    $currentVersion = Db::getInstance()->getValue($sql);

    if (
        $currentVersion &&
        version_compare($currentVersion, '1.4.3', '>=') &&
        version_compare($currentVersion, '2.0.0', '<=')
    ) {
        throw new \Alma\PrestaShop\Exceptions\AlmaException("Your current Alma module is in version ${currentVersion}. You need to disable the module before to finalize the migration");
    }
}
