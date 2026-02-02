<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use Media;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class SettingsController extends FrameworkBundleAdminController
{
    /**
     * @var PsAccountsService
     */
    private PsAccountsService $psAccountsService;

    public function __construct(PsAccountsService $psAccountsService)
    {
        $this->psAccountsService = $psAccountsService;
    }

    public function indexAction()
    {
        $errors = [];

        try {
            Media::addJsDef([
                'contextPsAccounts' => $this->psAccountsService->getPsAccountsPresenter()
                    ->present(),
            ]);
        } catch (ModuleNotInstalledException|ModuleVersionException|\Exception $e) {
            $errors[] = $e->getMessage();
            return '';
        }

        return $this->render(
            '@Modules/alma/views/templates/admin/settings.html.twig',
            [
                'title' => 'Alma Settings',
                'urlAccountsCdn' => $this->psAccountsService->getAccountsCdn(),
                'errors' => $errors
            ]
        );
    }
}
