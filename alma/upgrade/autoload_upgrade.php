<?php

// Need to reload the autoloader if files are added between versions
spl_autoload_register(function ($class) {
    $prefix = 'Alma\\PrestaShop\\';
    $baseDir = _PS_MODULE_DIR_ . 'alma/lib/';

    if (!class_exists($class)) {
        // Check if the class belongs to your namespace
        if (strpos($class, $prefix) === 0) {
            // Remove the namespace prefix
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
});
