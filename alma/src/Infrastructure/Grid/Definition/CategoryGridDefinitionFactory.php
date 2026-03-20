<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Definition;

use PrestaShop\Module\Alma\Infrastructure\Grid\Column\Type\IconBooleanColumn;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BulkActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CategoryGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    const GRID_ID = 'categories';

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
                (new ActionColumn('actions'))
                ->setName('Actions')
                    ->setOptions([
                        'actions' => (new RowActionCollection()),
                    ])
            )
            ->add(
                (new IconBooleanColumn('is_excluded'))
                    ->setName('Status')
                    ->setOptions(['field' => 'is_excluded'])
            );
    }

    protected function getFilters()
    {
        return (new FilterCollection())
            ->add(
                (new Filter('id_category', NumberType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => 'ID'],
                    ])
                    ->setAssociatedColumn('id_category')
            )
            ->add(
                (new Filter('name', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => 'Nom'],
                    ])
                    ->setAssociatedColumn('name')
            )
            ->add(
                (new Filter('description', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => ['placeholder' => 'Description'],
                    ])
                    ->setAssociatedColumn('description')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => CategoryGridDefinitionFactory::GRID_ID,
                            'filters_id' => CategoryGridDefinitionFactory::GRID_ID,
                        ],
                        'redirect_route' => 'alma_excluded_categories',
                    ])
                    ->setAssociatedColumn('actions')
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
            );
    }
}
