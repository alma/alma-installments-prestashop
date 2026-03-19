<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteria;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Grid\ResponseBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExcludedCategoriesController extends FrameworkBundleAdminController
{
    public function indexAction(Request $request): ?Response
    {
        $gridDefinitionFactory = $this->get('alma.category_grid_definition_factory');
        $gridDefinition = $gridDefinitionFactory->getDefinition();

        $categoryFilters = $this->get('prestashop.core.grid.filter.form_factory')
            ->create($gridDefinition);

        $categoryFilters->handleRequest($request);

        $filters = $categoryFilters->isSubmitted() && $categoryFilters->isValid()
            ? $categoryFilters->getData()
            : [];

        $searchCriteria = new SearchCriteria(
            $filters,        // filters
            'id_category', // orderBy
            'ASC',     // orderWay
            0,         // offset
            100         // limit
        );

        $categoryGridFactory = $this->get('alma.category_grid_factory');
        $categoryGrid = $categoryGridFactory->getGrid($searchCriteria);

        return $this->render(
            '@Modules/alma/views/templates/admin/excluded_categories.html.twig',
            ['categoryGrid' => $this->presentGrid($categoryGrid)]
        );
    }

    public function searchAction(Request $request): RedirectResponse
    {
        /** @var ResponseBuilder $responseBuilder */
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        return $responseBuilder->buildSearchResponse(
            $this->get('alma.category_grid_definition_factory'),
            $request,
            'categories',
            'alma_excluded_categories'
        );
    }
}
