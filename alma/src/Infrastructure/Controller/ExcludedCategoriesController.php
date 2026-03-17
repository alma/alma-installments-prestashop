<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class ExcludedCategoriesController extends FrameworkBundleAdminController
{
    public function indexAction()
    {
        return $this->render(
            '@Modules/alma/views/templates/admin/excluded_categories.html.twig',
            [
                'title' => 'My title',
                'key' => 'Value',
            ]
        );
    }
}
