<?php
/**
 * 2018-2023 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class LocaleHelper.
 *
 * Currency formatting/localization has been handled differently through PS versions, as they moved from a simple
 * enum (<1.7) to using CLDR data in PS 1.7.
 * Until PS 1.7.6, the IcanBoogie/CLDR library was being used; since PS 1.7.6, the PrestaShop team has implemented their
 * own CLDR data handling.
 *
 * This class is meant to help handle currency- and other locale-related data throughout PrestaShop versions, making
 * those differences transparent.
 */
class LocaleHelper
{
    /**
     * @var LanguageHelper
     */
    protected $languageHelper;

    /**
     *
     * @param $languageHelper
     */
    public function __construct($languageHelper)
    {
        $this->languageHelper = $languageHelper;
    }

    /**
     * Find the current decimal separator depending on the current context.
     *
     * @return string The decimal separator used for the current context (locale)
     *
     * @throws \Exception
     */
    public static function decimalSeparator()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $currencyFormat = \Context::getContext()->currency->format;

            switch ($currencyFormat) {
                case 1: // "X0,000.00"
                case 4: // "0,000.00X"
                case 5: // "0'000.00X"
                    return '.';
                case 2: // "0 000,00X"
                case 3: // "X0.000,00"
                    return ',';
                default:
                    throw new \Exception(sprintf('Currency format not supported %s', $currencyFormat));
            }
        }

        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $cldr = \Tools::getCldr(\Context::getContext());
            $culture = $cldr->getCulture();
            $locale = $cldr->getRepository()->locales[$culture];

            return $locale['numbers']['symbols-numberSystem-latn']['decimal'];
        }

        $currentLocale = \Context::getContext()->getCurrentLocale();
        $numberSpec = $currentLocale->getNumberSpecification();
        $symbols = $numberSpec->getSymbolsByNumberingSystem('latn');

        return $symbols->getDecimal();
    }

    /**
     * @return mixed|string
     *
     * @throws \Exception
     */
    public static function thousandSeparator()
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $currencyFormat = \Context::getContext()->currency->format;

            switch ($currencyFormat) {
                case 1: // "X0,000.00"
                case 4: // "0,000.00X"
                    return ',';
                case 2: // "0 000,00X"
                    return ' ';
                case 3: // "X0.000,00"
                    return '.';
                case 5: // "0'000.00X"
                    return "'";
                default:
                    throw new \Exception(sprintf('Currency format not supported %s', $currencyFormat));
            }
        }

        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
            $cldr = \Tools::getCldr(\Context::getContext());
            $culture = $cldr->getCulture();
            $locale = $cldr->getRepository()->locales[$culture];

            return $locale['numbers']['symbols-numberSystem-latn']['group'];
        }

        $currentLocale = \Context::getContext()->getCurrentLocale();
        $numberSpec = $currentLocale->getNumberSpecification();
        $symbols = $numberSpec->getSymbolsByNumberingSystem('latn');

        return $symbols->getGroup();
    }

    /**
     * Get translation by file iso if existed.
     *
     * @param array $string
     * @param string $source
     * @param string $locale
     *
     * @return array
     *
     * @see AdminTranslationsController::getModuleTranslation
     */
    public function getModuleTranslation(
        $string,
        $source,
        $iso
    ) {
        global $_MODULE, $_TRADS;

        $name = 'alma';

        $filesByPriority = [
            // PrestaShop 1.5 translations
            _PS_MODULE_DIR_ . $name . '/translations/' . $iso . '.php',
            // PrestaShop 1.4 translations
            _PS_MODULE_DIR_ . $name . '/' . $iso . '.php',
            // Translations in theme
            _PS_THEME_DIR_ . 'modules/' . $name . '/translations/' . $iso . '.php',
            _PS_THEME_DIR_ . 'modules/' . $name . '/' . $iso . '.php',
        ];
        foreach ($filesByPriority as $file) {
            if (file_exists($file)) {
                include $file;
                $_TRADS[$iso] = !empty($_TRADS[$iso]) ? array_merge($_TRADS[$iso], $_MODULE) : $_MODULE;
            }
        }

        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);

        $defaultKey = strtolower('<{' . $name . '}prestashop>' . $source) . '_' . $key;

        if (!empty($_TRADS[$iso][$defaultKey])) {
            $ret = stripslashes($_TRADS[$iso][$defaultKey]);
        } else {
            $ret = stripslashes($string);
        }
        $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');

        return $ret;
    }

    /**
     * Get locale by id_lang with condition NL for widget (provisional).
     *
     * @param int $idLang
     *
     * @return string
     */
    public function getLocaleByIdLangForWidget($idLang)
    {
        $locale = $this->languageHelper->getIsoById($idLang);

        if ('nl' == $locale) {
            $locale = 'nl-NL';
        }

        return $locale;
    }

    /**
     * @param \Context $context
     *
     * @return mixed
     */
    public function getLocaleFromContext($context)
    {
        $locale = $context->language->iso_code;

        if (property_exists($context->language, 'locale')) {
            $locale = $context->language->locale;
        }

        return $locale;
    }

    /**
     * Create a multilang field.
     * Same function as \PrestaShop\PrestaShop\Adapter\Import\ImportDataFormatter()->createMultiLangField()
     *
     * @param string $field
     *
     * @return array
     */
    public function createMultiLangField($field)
    {
        $result = [];

        foreach (\Language::getIDs(false) as $languageId) {
            $result[$languageId] = $field;
        }

        return $result;
    }
}
