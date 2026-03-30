<?php

namespace PrestaShop\Module\Alma\Infrastructure\Proxy;

/**
 * Class ProductProxy
 *
 * This class serves as a proxy to the Product class, allowing for easier testing and decoupling from the instantiation of Product.
 */
class ProductProxy
{
    /**
     * Return all category IDs a product belongs to.
     * @param int $productId
     * @return array
     */
    public function getCategories(int $productId): array
    {
        return (new \Product($productId))->getCategories();
    }
}
