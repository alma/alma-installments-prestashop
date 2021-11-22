<?php

namespace Alma\PrestaShop\Utils;

use PHPUnit\Framework\TestCase;

class LocaleHelperTest extends TestCase
{

    public function testGetModuleTranslationIfAllFilesArePresents()
    {
        define('_PS_MODULE_DIR_', __DIR__ .'/test_1');
        define('_PS_THEME_DIR_', __DIR__ . '/test_theme_1');

    }
    public function testGetModuleTranslationIfNoTranslationsFound()
    {
        define('_PS_MODULE_DIR_', __DIR__ .'/test_2');
        define('_PS_THEME_DIR_', __DIR__ . '/test_theme_2');

    }
}
