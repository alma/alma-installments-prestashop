<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FormService
{
    /**
     * @var ApiAdminForm
     */
    private ApiAdminForm $apiAdminForm;
    /**
     * @var FeePlansAdminForm
     */
    private FeePlansAdminForm $feePlansAdminForm;
    /**
     * @var FeePlansService
     */
    private FeePlansService $feePlansService;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        FeePlansService $feePlansService,
        ApiAdminForm $apiAdminForm,
        FeePlansAdminForm $feePlansAdminForm,
        ConfigurationRepository $configurationRepository
    ) {
        $this->feePlansService = $feePlansService;
        $this->apiAdminForm = $apiAdminForm;
        $this->feePlansAdminForm = $feePlansAdminForm;
        $this->configurationRepository = $configurationRepository;
    }

    public function getForm(): array
    {
        if (!empty($this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID))) {
            $templateTabs = $this->feePlansService->createTemplateTabs();
            $form[] = $this->feePlansAdminForm->build($templateTabs, $this->feePlansService->feePlansFields());
        }

        $form[] = $this->apiAdminForm->build();

        return $form;
    }
}
