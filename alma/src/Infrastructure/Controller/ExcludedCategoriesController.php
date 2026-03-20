<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Infrastructure\Grid\Filter\CategoryFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
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

    public function bulkExcludeAction(Request $request): RedirectResponse
    {
        $categoryIds = $request->request->get('alma_excluded_categories_bulk', []);
        /** @var ExcludedCategoriesService $excludedCategoriesService */
        $excludedCategoriesService = $this->get('alma.excluded_categories_service');
        $excludedCategoriesService->addExcludeCategories($categoryIds);

        return $this->redirectToRoute('alma_excluded_categories');
    }

    public function bulkIncludeAction(Request $request): RedirectResponse
    {
        $categoryIds = $request->request->get('alma_excluded_categories_bulk', []);
        /** @var ExcludedCategoriesService $excludedCategoriesService */
        $excludedCategoriesService = $this->get('alma.excluded_categories_service');
        $excludedCategoriesService->removeExcludeCategories($categoryIds);

        return $this->redirectToRoute('alma_excluded_categories');
    }

    public function bulkExcludeAction(Request $request): RedirectResponse
    {
        $categoryIds = $request->request->get('alma_excluded_categories_bulk', []);
        /** @var ExcludedCategoriesService $excludedCategoriesService */
        $excludedCategoriesService = $this->get('alma.excluded_categories_service');
        $excludedCategoriesService->addExcludeCategories($categoryIds);

        return $this->redirectToRoute('alma_excluded_categories');
    }

    public function bulkIncludeAction(Request $request): RedirectResponse
    {
        $categoryIds = $request->request->get('alma_excluded_categories_bulk', []);
        /** @var ExcludedCategoriesService $excludedCategoriesService */
        $excludedCategoriesService = $this->get('alma.excluded_categories_service');
        $excludedCategoriesService->removeExcludeCategories($categoryIds);

        return $this->redirectToRoute('alma_excluded_categories');
    }

    public function bulkExcludeAction(Request $request): RedirectResponse
    {
        $categoryIds = $request->request->get('alma_excluded_categories_bulk', []);
        /** @var ExcludedCategoriesService $excludedCategoriesService */
        $excludedCategoriesService = $this->get('alma.excluded_categories_service');
        $excludedCategoriesService->addExcludeCategories($categoryIds);

        return $this->redirectToRoute('alma_excluded_categories');
    }

    public function bulkIncludeAction(Request $request): RedirectResponse
    {
        $categoryIds = $request->request->get('alma_excluded_categories_bulk', []);
        /** @var ExcludedCategoriesService $excludedCategoriesService */
        $excludedCategoriesService = $this->get('alma.excluded_categories_service');
        $excludedCategoriesService->removeExcludeCategories($categoryIds);

        return $this->redirectToRoute('alma_excluded_categories');
    }
}
