<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Definition;

use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BulkActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CategoryGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    protected function getId(): string
    {
        return 'categories';
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
                (new DataColumn('active'))
                    ->setName('Active')
                    ->setOptions([
                        'field' => 'active',
                        'sortable' => false,
                    ])
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
