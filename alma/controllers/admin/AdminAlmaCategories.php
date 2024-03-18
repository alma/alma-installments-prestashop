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
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ShopHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminAlmaCategoriesController extends ModuleAdminController
{
    public static $excludedCategories = [];
    private $excludeFilter;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'category';
        $this->className = 'Category';
        $this->lang = true;
        $this->deleted = false;
        $this->explicitSelect = false;
        $this->_defaultOrderBy = 'position';
        $this->allow_export = false;
        $this->list_no_link = true;
        $this->settingsHelper = new SettingsHelper(new ShopHelper(), new ConfigurationHelper());

        parent::__construct();

        $this->fields_list = [
            'id_category' => [
                'title' => $this->module->l('ID', 'AdminAlmaCategories'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->module->l('Name', 'AdminAlmaCategories'),
                'filter_key' => 'b!name',
            ],
            'description' => [
                'title' => $this->module->l('Description', 'AdminAlmaCategories'),
                'filter_key' => 'b!description',
                'callback' => 'getDescriptionClean',
                'orderby' => false,
            ],
            'parent' => [
                'title' => $this->module->l('Parent', 'AdminAlmaCategories'),
                'filter_key' => 'cpl!name',
            ],
            'excluded' => [
                'title' => $this->module->l('Alma Eligible', 'AdminAlmaCategories'),
                'callback' => 'getExcluded',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false,
            ],
        ];

        $this->bulk_actions = [
            'enable' => [
                'text' => $this->module->l('Enable Alma for these categories', 'AdminAlmaCategories'),
                'icon' => 'icon-check text-success',
            ],
            'disable' => [
                'text' => $this->module->l('Disable Alma for these categories', 'AdminAlmaCategories'),
                'icon' => 'icon-ban text-danger',
            ],
        ];

        static::$excludedCategories = $this->settingsHelper->getExcludedCategories();
    }

    public function init()
    {
        parent::init();

        $this->_select = 'a.`id_category`, b.`name`, b.`description`, b.`name` as `parent`, a.`id_category` as `excluded`';
        $this->_use_found_rows = false;

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` sa ON (a.`id_category` = sa.`id_category`';
            $this->_join .= ' AND sa.id_shop = ' . (int) $this->context->shop->id . ') ';
        } else {
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` sa ON (a.`id_category` = sa.`id_category` ';
            $this->_join .= ' AND sa.id_shop = a.id_shop_default) ';
        }

        // we add restriction for shop
        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND sa.`id_shop` = ' . (int) Context::getContext()->shop->id;
        }

        // if we are not in a shop context, we remove the position column
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            unset($this->fields_list['position']);
        }
    }

    public function processFilter()
    {
        $this->excludeFilter = null;
        $excludeFilterKey = 'almacategoriescategoryFilter_excluded';
        Hook::exec('action' . $this->controller_name . 'ListingFieldsModifier', [
            'fields' => &$this->fields_list,
        ]);

        if (!isset($this->list_id)) {
            $this->list_id = $this->table;
        }

        if (method_exists('AdminControllerCore', 'getCookieFilterPrefix')) {
            $prefix = $this->getCookieFilterPrefix();
        } else {
            $prefix = str_replace(['admin', 'controller'], '', Tools::strtolower(get_class($this)));
        }

        if (isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '') {
                    unset($this->context->cookie->{$prefix . $key});
                } elseif (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : json_encode($value);
                } else {
                    if (stripos($key, 'submitFilter') === 0) {
                        $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                    }
                }
            }

            foreach ($_GET as $key => $value) {
                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : json_encode($value);
                } else {
                    if (stripos($key, 'submitFilter') === 0) {
                        $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                    }
                }
                if (stripos($key, $this->list_id . 'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $this->_defaultOrderBy) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                } else {
                    if (stripos($key, $this->list_id . 'Orderway') === 0 && Validate::isOrderWay($value)) {
                        if ($value === '' || $value == $this->_defaultOrderWay) {
                            unset($this->context->cookie->{$prefix . $key});
                        } else {
                            $this->context->cookie->{$prefix . $key} = $value;
                        }
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');
        foreach ($filters as $key => $filter) {
            // Extract our custom excluded filter here and remove it from the filters list to avoid a SQL error
            if ($key === $excludeFilterKey) {
                $this->excludeFilter = (int) $filter;
                unset($filters[$key]);
            }
        }
        $definition = false;
        if (isset($this->className) && $this->className) {
            $definition = ObjectModel::getDefinition($this->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if (
                $value != null
                && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + Tools::strlen($prefix . $this->list_id))
            ) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    if (array_key_exists('filter_type', $field)) {
                        $type = $field['filter_type'];
                    } else {
                        $type = array_key_exists('type', $field) ? $field['type'] : false;
                    }
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = json_decode($value, true);
                    }
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0] . '.`' . $tmp_tab[1] . '`' : '`' . $tmp_tab[0] . '`';

                    // Assignment by reference
                    if (array_key_exists('tmpTableFilter', $field)) {
                        $sql_filter = &$this->_tmpTableFilter;
                    } elseif (array_key_exists('havingFilter', $field)) {
                        $sql_filter = &$this->_filterHaving;
                    } else {
                        $sql_filter = &$this->_filter;
                    }

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = $this->trans(
                                    'The \'From\' date format is invalid (YYYY-MM-DD)',
                                    [],
                                    'Admin.Notifications.Error'
                                );
                            } else {
                                $sql_filter .= (
                                    ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\''
                                );
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = $this->trans(
                                    'The \'To\' date format is invalid (YYYY-MM-DD)',
                                    [],
                                    'Admin.Notifications.Error'
                                );
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == $this->identifier || $key == '`' . $this->identifier . '`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            if ($check_key || $key == '`active`') {
                                $sql_filter .= ($alias . '.') . pSQL($key) . ' = ' . (int) $value . ' ';
                            } else {
                                $sql_filter .= '' . pSQL($key) . ' = ' . (int) $value . ' ';
                            }
                        } elseif ($type == 'decimal') {
                            if ($check_key) {
                                $sql_filter .= ($alias . '.') . pSQL($key) . ' = ' . (float) $value . ' ';
                            } else {
                                $sql_filter .= '' . pSQL($key) . ' = ' . (float) $value . ' ';
                            }
                        } elseif ($type == 'select') {
                            if ($check_key) {
                                $sql_filter .= ($alias . '.') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                            } else {
                                $sql_filter .= '' . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                            }
                        } elseif ($type == 'price') {
                            $value = (float) str_replace(',', '.', $value);
                            if ($check_key) {
                                $sql_filter .= ($alias . '.') . pSQL($key) . ' = ' . pSQL(trim($value)) . ' ';
                            } else {
                                $sql_filter .= '' . pSQL($key) . ' = ' . pSQL(trim($value)) . ' ';
                            }
                        } else {
                            if ($check_key) {
                                $sql_filter .= ($alias . '.') . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                            } else {
                                $sql_filter .= '' . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                            }
                        }
                    }
                }
            }
        }
    }

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        if (1 === $this->excludeFilter) {
            foreach ($this->_list as $k => $list) {
                foreach (static::$excludedCategories as $excluded) {
                    if ($list['id_category'] === $excluded) {
                        unset($this->_list[$k]);
                    }
                }
            }
        } elseif (0 === $this->excludeFilter) {
            $tmp = [];
            foreach (static::$excludedCategories as $excluded) {
                foreach ($this->_list as $list) {
                    if ($list['id_category'] === $excluded) {
                        $tmp[] = $list;
                    }
                }
            }
            $this->_list = $tmp;
        }
    }

    public function initToolbar()
    {
        return false;
    }

    public function renderView()
    {
        $this->initToolbar();

        return $this->renderList();
    }

    /**
     * AdminController::renderList() override.
     *
     * @return string
     *
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        $this->addRowAction('voidAction');

        return parent::renderList();
    }

    /**
     * No button action is table list
     *
     * @return string
     */
    public function displayVoidActionLink()
    {
        return '';
    }

    /**
     * processBulkEnable REMOVE Excluded Categories from ALMA_EXCLUDED_CATEGORIES
     *
     * @return void
     */
    protected function processBulkEnable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $id_category) {
            $category = new Category((int) $id_category);

            if (Validate::isLoadedObject($category)) {
                $this->settingsHelper->removeExcludedCategories((int) $id_category);
            }
        }

        // need to force page refresh here for obscure reason
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAlmaCategories'));
    }

    /**
     * processBulkEnable INSERT Excluded Categories in ALMA_EXCLUDED_CATEGORIES
     *
     * @return void
     */
    protected function processBulkDisable()
    {
        foreach (Tools::getValue($this->table . 'Box') as $id_category) {
            $category = new Category((int) $id_category);

            if (Validate::isLoadedObject($category)) {
                $this->settingsHelper->addExcludedCategories((int) $id_category);
            }
        }

        // need to force page refresh here for obscure reason
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminAlmaCategories'));
    }

    public static function getExcluded($id_category)
    {
        if (in_array($id_category, static::$excludedCategories)) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                return '<i class="icon-ban text-danger"></i>';
            }

            return '❌';
        }
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            return '<i class="icon-check text-success"></i>';
        }

        return '✅';
    }

    public static function getDescriptionClean($description)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return Tools::getDescriptionClean($description);
        } else {
            return strip_tags(Tools::stripslashes($description));
        }
    }
}
