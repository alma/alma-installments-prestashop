<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

class AdminAlmaCategoriesController extends ModuleAdminController
{
    private $original_filter = '';
    static $exclude_categories = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'category';
        $this->className = 'Category';
        $this->lang = true;
        $this->deleted = false;
        $this->explicitSelect = false;
        $this->_defaultOrderBy = 'position';
        $this->allow_export = true;
        $this->list_no_link  = true;
        
        

        parent::__construct();

        $this->fieldImageSettings = array(
            'name' => 'image',
            'dir' => 'c',
        );

        $this->fields_list = array(
            'id_category' => array(
                'title' => $this->module->l('ID', 'AdminAlmaCategories'),               
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'name' => array(
                'title' => $this->module->l('Name', 'AdminAlmaCategories'),
                'filter_key' => 'b!name',
            ),
            'description' => array(
                'title' => $this->module->l('Description', 'AdminAlmaCategories'),
                'filter_key' => 'b!description',
                'callback' => 'getDescriptionClean',
                'orderby' => false,
            ),
            'parent' => array(
                'title' => $this->module->l('Parent', 'AdminAlmaCategories'),
                'filter_key' => 'cpl!name',
            ),
            'exclude' => array(
                'title' => $this->module->l('Exclude', 'AdminAlmaCategories'),
                'callback' => 'getExclude',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false,
            ),
        );

        $this->bulk_actions = array(
            'enable' => array(
                'text' => $this->module->l('Exclude selected', 'AdminAlmaCategories'),
                'icon' => 'icon-power-off text-success',
                'confirm' => $this->module->l('Exclude selected items?', 'AdminAlmaCategories'),
            ),
            'disable' => array(
                'text' => $this->module->l('Include selected', 'AdminAlmaCategories'),
                'icon' => 'icon-power-off text-danger',
                'confirm' => $this->module->l('Include selected items?', 'AdminAlmaCategories'),
                
            ),
        );

        self::$exclude_categories = AlmaSettings::getExcludeCategories();
    }

    public function init()
    {
        parent::init();

        $this->_select = 'a.`id_category`, b.`name`, b.`description`, cpl.`name` as `parent`, a.`id_category` as `exclude`';
        $this->_use_found_rows = false;

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` sa ON (a.`id_category` = sa.`id_category` AND sa.id_shop = ' . (int) $this->context->shop->id . ') ';
        } else {
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_shop` sa ON (a.`id_category` = sa.`id_category` AND sa.id_shop = a.id_shop_default) ';
        }
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category` cp ON (a.`id_parent` = cp.`id_category`)
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cpl ON (cp.`id_category` = cpl.`id_category`) ';

        // we add restriction for shop
        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND sa.`id_shop` = ' . (int) Context::getContext()->shop->id;
        }

        // if we are not in a shop context, we remove the position column
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            unset($this->fields_list['position']);
        }

        $this->_group = 'GROUP BY a.`id_category`';
        
    }


    public function initToolbar(){
        return false;
    }
    

    public function renderView()
    {
        $this->initToolbar();
        

        return $this->renderList();
    }


    public function postProcess()
    {
        return parent::postProcess();
    }

    protected function processBulkEnable()
    {
        $cats_ids = array();
        foreach (Tools::getValue($this->table . 'Box') as $id_category) {
            $category = new Category((int) $id_category);
            if (Validate::isLoadedObject($category)) {
                AlmaSettings::removeExcludeCategories($id_category);
            }
        }
        header("Location:" .$this->context->link->getAdminLink('AdminAlmaCategories'));
    }

    protected function processBulkDisable()
    {
        $cats_ids = array();
        foreach (Tools::getValue($this->table . 'Box') as $id_category) {
            $category = new Category((int)$id_category);
            if (Validate::isLoadedObject($category)) {
                AlmaSettings::addExcludeCategories($id_category);
            }
        }        
        header("Location:" .$this->context->link->getAdminLink('AdminAlmaCategories'));        
    }

    public function ajaxProcessExcludeCategory()
    {
        if (!$id_category = (int)Tools::getValue('id_category')) {
            die(Tools::jsonEncode(array('success' => false, 'error' => true, 'text' => $this->l('Failed to update the status'))));
        } else {
            $category = new Category((int)$id_category);
            if (Validate::isLoadedObject($category)) {
                AlmaSettings::addExcludeCategories($id_category);
                die(Tools::jsonEncode(array('success' => true, 'text' => $this->l('The status has been updated successfully'))));
            }
        }
    }

    public static function getExclude($id_category)
    {
        if (in_array($id_category, self::$exclude_categories)) {
            return '✅';
        }
        return '❌';
    }

    public static function getDescriptionClean($description)
    {
        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            return Tools::getDescriptionClean($description);
        }
        else{
            return strip_tags(stripslashes($description));
        }
        
    }
}
