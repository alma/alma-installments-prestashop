<?php

namespace PrestaShop\Module\Alma\Infrastructure\Controller;

use Alma;
use Media;
use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class SettingsController extends FrameworkBundleAdminController
{
    public const FIELDS_FORM = [
        'ALMA_MODE' => [
            'type' => 'select',
            'required' => true,
            'form' => 'api',
            'options' => []
        ],
        'ALMA_API_KEY_TEST',
        'ALMA_API_KEY_LIVE',
    ];
    /**
     * @var PsAccountsService
     */
    private PsAccountsService $psAccountsService;
    private Alma $module;
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;

    public function __construct(
        Alma $module,
        PsAccountsService $psAccountsService,
        SettingsService $settingsService
    ) {
        $this->module = $module;
        $this->psAccountsService = $psAccountsService;
        $this->settingsService = $settingsService;
    }

    /**
     * Render the settings page
     */
    public function indexAction()
    {
        $errors = [];
        $urlAccountsCdn = '';
        $displayPsAccounts = true;
        $isAccountLinked = false;

        try {
            Media::addJsDef([
                'contextPsAccounts' => $this->psAccountsService->getPsAccountsPresenter()
                    ->present(),
            ]);
            $urlAccountsCdn = $this->psAccountsService->getAccountsCdn();
            // TODO : Verification is been in PHP but can be check in JS, need to wait the configuration form to check the best usage
            $isAccountLinked = $this->psAccountsService->isAccountLinked();
        } catch (PsAccountsException|\Exception $e) {
            $errors[] = $e->getMessage();
            $displayPsAccounts = false;
        }

        return $this->render(
            '@Modules/alma/views/templates/admin/settings.html.twig',
            [
                'title' => 'Alma Settings',
                'displayPsAccounts' => $displayPsAccounts,
                'isPsAccountsLinked' => $isAccountLinked,
                'urlAccountsCdn' => $urlAccountsCdn,
                'errors' => $errors,
                'form' => $this->settingsService->getFormFromHelperForm(),
            ]
        );
    }
}
