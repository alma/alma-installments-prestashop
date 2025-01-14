<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Proxy;

use Alma\PrestaShop\Factories\ContextFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class HelperFormProxy
{
    /**
     * @var \Context
     */
    private $context;
    /**
     * @var \HelperForm
     */
    private $helperForm;

    public function __construct(
        $context = null,
        $helperForm = null
    ) {
        if (!$context) {
            $context = new ContextFactory();
        }
        $this->context = $context;
        if (!$helperForm) {
            $helperForm = new \HelperForm();
        }
        $this->helperForm = $helperForm;
    }

    /**
     * Use the HelperForm from Prestashop to build the default data for configuration form
     *
     * @return string
     */
    public function getHelperForm($formFields)
    {
        return $this->helperForm->generateForm($formFields);
    }

    /**
     * Setter for the module
     *
     * @param $module
     *
     * @return void
     */
    public function setModule($module)
    {
        $this->helperForm->module = $module;
    }

    /**
     * Setter for the table
     *
     * @param $table
     *
     * @return void
     */
    public function setTable($table)
    {
        $this->helperForm->table = $table;
    }

    /**
     * Setter for the default form language
     *
     * @param $defaultFormLanguage
     *
     * @return void
     */
    public function setDefaultFormLanguage($defaultFormLanguage)
    {
        $this->helperForm->default_form_language = $defaultFormLanguage;
    }

    /**
     * Setter for the allow employee form lang
     *
     * @param $allowEmployeeFormLang
     *
     * @return void
     */
    public function setAllowEmployeeFormLang($allowEmployeeFormLang)
    {
        $this->helperForm->allow_employee_form_lang = $allowEmployeeFormLang;
    }

    /**
     * Setter for the submit action
     *
     * @param $submitAction
     *
     * @return void
     */
    public function setSubmitAction($submitAction)
    {
        $this->helperForm->submit_action = $submitAction;
    }

    /**
     * Setter for the base folder (only used for Prestashop < 1.6)
     *
     * @param $baseFolder
     *
     * @return void
     */
    public function setBaseFolder($baseFolder)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->helperForm->base_folder = $baseFolder;
        }
    }

    /**
     * Setter assets for only Prestashop < 1.6
     *
     * @param array $assetsCss
     *
     * @return void
     */
    public function setAssetsCss($assetsCss)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            foreach ($assetsCss as $assetCss) {
                $this->context->controller->addCSS($assetCss);
            }
        }
    }

    /**
     * Setter for the current index
     *
     * @param $currentIndex
     *
     * @return void
     */
    public function setCurrentIndex($currentIndex)
    {
        $this->helperForm->currentIndex = $currentIndex;
    }

    /**
     * Setter for the token
     *
     * @param $token
     *
     * @return void
     */
    public function setToken($token)
    {
        $this->helperForm->token = $token;
    }

    /**
     * Getter for the fields value
     *
     * @return array
     */
    public function getFieldsValue()
    {
        return $this->helperForm->fields_value;
    }

    /**
     * Setter for the fields value
     *
     * @param $fieldsValue
     *
     * @return void
     */
    public function setFieldsValue($fieldsValue)
    {
        $this->helperForm->fields_value = $fieldsValue;
    }

    /**
     * Setter for the languages
     *
     * @param $languages
     *
     * @return void
     */
    public function setLanguages($languages)
    {
        $this->helperForm->languages = $languages;
    }
}
