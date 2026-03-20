<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use PrestaShop\Module\Alma\Infrastructure\Grid\Filter\CategoryFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Grid\ResponseBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExcludedCategoriesController extends FrameworkBundleAdminController
{
    public function indexAction(): ?Response
    {
        $categoryFilters = new CategoryFilters(CategoryFilters::getDefaults());

        $categoryGridFactory = $this->get('alma.category_grid_factory');
        $categoryGrid = $categoryGridFactory->getGrid($categoryFilters);

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

    public function bulkExcludeAction(Request $request): RedirectResponse
    {
        return $this->redirectToRoute('alma_excluded_categories');
    }
}
