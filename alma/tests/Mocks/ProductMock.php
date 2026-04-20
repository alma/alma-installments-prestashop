<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

final class ProductMock
{
    public static function productArray(): array
    {
        return [
            'id_product_attribute' => '1',
            'id_product' => '1',
            'id_shop' => '1',
            'name' => 'Test Product',
            'is_virtual' => '0',
            'id_category_default' => '8',
            'price' => 10.00,
            'active' => '1',
            'quantity' => '1',
            'category' => 'home-accessories'
        ];
    }
}
