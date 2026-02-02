<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class SettingsController extends FrameworkBundleAdminController
{
    public function indexAction()
    {
        return $this->render(
            '@Modules/alma/views/templates/admin/settings.html.twig',
            [
                'title' => 'Alma Settings',
            ]
        );
    }
}
