<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Definition;

use PrestaShop\Module\Alma\Infrastructure\Grid\Column\Type\IconBooleanColumn;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BulkActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractFilterableGridDefinitionFactory;

final class CategoryGridDefinitionFactory extends AbstractFilterableGridDefinitionFactory
{
    const GRID_ID = 'alma_excluded_categories';

    protected function getId(): string
    {
        return self::GRID_ID;
    }

    protected function getName(): string
    {
        return 'Categories';
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add(
                (new BulkActionColumn('bulk'))
                    ->setOptions([
                        'bulk_field' => 'id_category',
                    ])
            )
            ->add(
                (new DataColumn('id_category'))
                    ->setName('ID')
                    ->setOptions([
                        'field' => 'id_category',
                    ])
            )
            ->add(
                (new DataColumn('name'))
                    ->setName('Nom')
                    ->setOptions([
                        'field' => 'name',
                    ])
            )
            ->add(
                (new DataColumn('description'))
                    ->setName('Description')
                    ->setOptions([
                        'field' => 'description',
                    ])
            )
            ->add(
                (new IconBooleanColumn('is_excluded'))
                    ->setName('Status')
                    ->setOptions(['field' => 'is_excluded'])
            );
    }

    protected function getGridActions(): GridActionCollection
    {
        return new GridActionCollection();
    }

    protected function getBulkActions()
    {
        return (new BulkActionCollection())
            ->add(
                (new SubmitBulkAction('exclude_categories'))
                    ->setName('Exclure les catégories sélectionnées')
                    ->setOptions([
                        'submit_route' => 'alma_excluded_categories_bulk_exclude',
                    ])
            )
            ->add(
                (new SubmitBulkAction('include_categories'))
                    ->setName('Inclure les catégories sélectionnées')
                    ->setOptions([
                        'submit_route' => 'alma_excluded_categories_bulk_include',
                    ])
            );
    }
}
