<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use Media;
use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
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

    /**
     * Render the settings page
     */
    public function indexAction()
    {
        $errors = [];
        $urlAccountsCdn = '';
        $displayPsAccounts = true;

        try {
            Media::addJsDef([
                'contextPsAccounts' => $this->psAccountsService->getPsAccountsPresenter()
                    ->present(),
            ]);
            $urlAccountsCdn = $this->psAccountsService->getAccountsCdn();
        } catch (PsAccountsException|\Exception $e) {
            $errors[] = $e->getMessage();
            $displayPsAccounts = false;
        }

        return $this->render(
            '@Modules/alma/views/templates/admin/settings.html.twig',
            [
                'title' => 'Alma Settings',
                'displayPsAccounts' => $displayPsAccounts,
                'urlAccountsCdn' => $urlAccountsCdn,
                'errors' => $errors
            ]
        );
    }
}
