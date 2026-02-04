<?php

namespace PrestaShop\Module\Alma\Infrastructure\Model;

use PrestaShop\PrestaShop\Adapter\Entity\ObjectModel;

class AlmaBusinessModel extends ObjectModel
{
    /** @var int */
    public int $id_cart;

    /** @var string */
    public int $orders;

    /** @var string */
    public string $alma_payment_id;

    /** @var bool */
    public bool $is_bnpl_eligible;

    /** @var string */
    public string $plan_key;

    /** @var string */
    public string $date_add;

    /** @var string */
    public string $date_upd;

    public static $definition = [
        'table' => 'alma',
        'primary' => 'id_cart',
        'multishop' => true,
        'fields' => [
            'id_cart' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'orders' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false],
            'alma_payment_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 64, 'required' => false],
            'is_bnpl_eligible' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
            'plan_key' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 32, 'required' => false],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true]
        ],
    ];
}
