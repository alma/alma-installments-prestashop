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

namespace Alma\PrestaShop\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InsuranceProduct extends \ObjectModel
{
    /** @var int Id */
    public $id_alma_insurance_product;

    /** @var int Id cart */
    public $id_cart;

    /** @var int Id product */
    public $id_product;

    /** @var int Id shop */
    public $id_shop;

    /** @var int Id product attribute */
    public $id_product_attribute;

    /** @var int Id customization */
    public $id_customization;

    /** @var int Id product insurance */
    public $id_product_insurance;

    /** @var int Id product attribute insurance */
    public $id_product_attribute_insurance;

    /** @var int Id address delivery */
    public $id_address_delivery;

    /** @var int Id cart */
    public $id_order;

    /** @var float Price of insurance */
    public $price;

    /** @var float Price of the product */
    public $product_price;

    /** @var float Subscription price Neat */
    public $subscription_amount;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object validity */
    public $subscription_state;

    /** @var string Neat insurance id */
    public $subscription_id;

    /** @var string Neat client insurance id */
    public $subscription_broker_id;

    /** @var string Neat client insurance reference */
    public $subscription_broker_reference;

    /** @var string Alma cms reference */
    public $cms_reference;

    /** @var string Object cancellation date */
    public $date_of_cancelation;

    /** @var string Object request cancellation date */
    public $date_of_cancelation_request;

    /** @var string reason of cancellation */
    public $reason_of_cancelation;

    /** @var bool Object refunded state */
    public $is_refunded;

    /** @var string Object refund date */
    public $date_of_refund;

    /** @var string Live ou test */
    public $mode;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'alma_insurance_product',
        'primary' => 'id_alma_insurance_product',
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_customization' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_product_insurance' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product_attribute_insurance' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_address_delivery' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'insurance_contract_id' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'cms_reference' => ['type' => self::TYPE_STRING],
            'product_price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'subscription_id' => ['type' => self::TYPE_STRING],
            'subscription_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'subscription_broker_id' => ['type' => self::TYPE_STRING],
            'subscription_broker_reference' => ['type' => self::TYPE_STRING],
            'subscription_state' => ['type' => self::TYPE_STRING],
            'date_of_cancelation' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_of_cancelation_request' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'reason_of_cancelation' => ['type' => self::TYPE_STRING],
            'is_refunded' => ['type' => self::TYPE_BOOL],
            'date_of_refund' => ['type' => self::TYPE_DATE,  'validate' => 'isDate'],
            'mode' => ['type' => self::TYPE_STRING],
        ],
    ];

    /**
     * @param $null_values
     * @param $auto_date
     *
     * @return bool|int|string
     *
     * @throws \PrestaShopException
     */
    public function save($null_values = false, $auto_date = true)
    {
        if (version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
            return $this->updateForNamespace($null_values);
        } else {
            return parent::save($null_values, $auto_date);
        }
    }

    /**
     * Updates the current object in the database
     *
     * @param bool $null_values
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    private function updateForNamespace($null_values = false)
    {
        // @hook actionObject*UpdateBefore
        \Hook::exec('actionObjectUpdateBefore', ['object' => $this]);
        \Hook::exec('actionObject' . $this->getFullyQualifiedName() . 'UpdateBefore', ['object' => $this]);

        $this->clearCache();

        // Automatically fill dates
        if (array_key_exists('date_upd', $this)) {
            $this->date_upd = date('Y-m-d H:i:s');
            if (isset($this->update_fields) && is_array($this->update_fields) && count($this->update_fields)) {
                $this->update_fields['date_upd'] = true;
            }
        }

        // Automatically fill dates
        if (array_key_exists('date_add', $this) && $this->date_add == null) {
            $this->date_add = date('Y-m-d H:i:s');
            if (isset($this->update_fields) && is_array($this->update_fields) && count($this->update_fields)) {
                $this->update_fields['date_add'] = true;
            }
        }

        $id_shop_list = \Shop::getContextListShopID();
        if (count($this->id_shop_list) > 0) {
            $id_shop_list = $this->id_shop_list;
        }

        if (\Shop::checkIdShopDefault($this->def['table']) && !$this->id_shop_default) {
            $this->id_shop_default = (in_array(\Configuration::get('PS_SHOP_DEFAULT'), $id_shop_list) == true) ? \Configuration::get('PS_SHOP_DEFAULT') : min($id_shop_list);
        }
        // Database update
        if (!$result = \Db::getInstance()->update($this->def['table'], $this->getFields(), '`' . pSQL($this->def['primary']) . '` = ' . (int) $this->id, 0, $null_values)) {
            return false;
        }

        // Database insertion for multishop fields related to the object
        if (\Shop::isTableAssociated($this->def['table'])) {
            $fields = $this->getFieldsShop();
            $fields[$this->def['primary']] = (int) $this->id;
            if (is_array($this->update_fields)) {
                $update_fields = $this->update_fields;
                $this->update_fields = null;
                $all_fields = $this->getFieldsShop();
                $all_fields[$this->def['primary']] = (int) $this->id;
                $this->update_fields = $update_fields;
            } else {
                $all_fields = $fields;
            }

            foreach ($id_shop_list as $id_shop) {
                $fields['id_shop'] = (int) $id_shop;
                $all_fields['id_shop'] = (int) $id_shop;
                $where = $this->def['primary'] . ' = ' . (int) $this->id . ' AND id_shop = ' . (int) $id_shop;

                // A little explanation of what we do here : we want to create multishop entry when update is called, but
                // only if we are in a shop context (if we are in all context, we just want to update entries that alread exists)
                $shop_exists = \Db::getInstance()->getValue('SELECT ' . $this->def['primary'] . ' FROM ' . _DB_PREFIX_ . $this->def['table'] . '_shop WHERE ' . $where);
                if ($shop_exists) {
                    $result &= \Db::getInstance()->update($this->def['table'] . '_shop', $fields, $where, 0, $null_values);
                } elseif (\Shop::getContext() == \Shop::CONTEXT_SHOP) {
                    $result &= \Db::getInstance()->insert($this->def['table'] . '_shop', $all_fields, $null_values);
                }
            }
        }

        // Database update for multilingual fields related to the object
        if (isset($this->def['multilang']) && $this->def['multilang']) {
            $fields = $this->getFieldsLang();
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    foreach (array_keys($field) as $key) {
                        if (!\Validate::isTableOrIdentifier($key)) {
                            throw new \PrestaShopException('key ' . $key . ' is not a valid table or identifier');
                        }
                    }

                    // If this table is linked to multishop system, update / insert for all shops from context
                    if ($this->isLangMultishop()) {
                        $id_shop_list = \Shop::getContextListShopID();
                        if (count($this->id_shop_list) > 0) {
                            $id_shop_list = $this->id_shop_list;
                        }
                        foreach ($id_shop_list as $id_shop) {
                            $field['id_shop'] = (int) $id_shop;
                            $where = pSQL($this->def['primary']) . ' = ' . (int) $this->id
                                . ' AND id_lang = ' . (int) $field['id_lang']
                                . ' AND id_shop = ' . (int) $id_shop;

                            if (\Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . pSQL(_DB_PREFIX_ . $this->def['table']) . '_lang WHERE ' . $where)) {
                                $result &= \Db::getInstance()->update($this->def['table'] . '_lang', $field, $where);
                            } else {
                                $result &= \Db::getInstance()->insert($this->def['table'] . '_lang', $field);
                            }
                        }
                    }
                    // If this table is not linked to multishop system ...
                    else {
                        $where = pSQL($this->def['primary']) . ' = ' . (int) $this->id
                            . ' AND id_lang = ' . (int) $field['id_lang'];
                        if (\Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . pSQL(_DB_PREFIX_ . $this->def['table']) . '_lang WHERE ' . $where)) {
                            $result &= \Db::getInstance()->update($this->def['table'] . '_lang', $field, $where);
                        } else {
                            $result &= \Db::getInstance()->insert($this->def['table'] . '_lang', $field, $null_values);
                        }
                    }
                }
            }
        }

        // @hook actionObject*UpdateAfter
        \Hook::exec('actionObjectUpdateAfter', ['object' => $this]);
        \Hook::exec('actionObject' . $this->getFullyQualifiedName() . 'UpdateAfter', ['object' => $this]);

        return $result;
    }

    /**
     * @return array|string|string[]
     */
    private function getFullyQualifiedName()
    {
        return str_replace('\\', '', get_class($this));
    }
}
