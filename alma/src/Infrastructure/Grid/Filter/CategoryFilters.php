<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Filter;

use PrestaShop\Module\Alma\Infrastructure\Grid\Definition\CategoryGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Search\Filters;

class CategoryFilters extends Filters
{
    protected $filterId = CategoryGridDefinitionFactory::GRID_ID;

    public static function getDefaults(): array
    {
        return [
            'limit' => 100,
            'offset' => 0,
            'orderBy' => 'id_category',
            'sortOrder' => 'ASC',
            'filters' => [],
        ];
    }
}
