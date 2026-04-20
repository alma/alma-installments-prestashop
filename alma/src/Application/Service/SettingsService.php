<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

class SettingsService
{
    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;
    /**
     * @var FeePlansService
     */
    private FeePlansService $feePlansService;
    /**
     * @var WidgetService
     */
    private WidgetService $widgetService;
    /**
     * @var ExcludedCategoriesService
     */
    private ExcludedCategoriesService $excludedCategoriesService;
    /**
     * @var RefundService
     */
    private RefundService $refundService;
    /**
     * @var InPageService
     */
    private InPageService $inPageService;
    /**
     * @var PaymentButtonService
     */
    private PaymentButtonService $paymentButtonService;
    /**
     * @var AuthenticationSettingsProvider
     */
    private AuthenticationSettingsProvider $authenticationSettingsProvider;
    /**
     * @var FeePlansProvider
     */
    private FeePlansProvider $feePlansProvider;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        AuthenticationService $authenticationService,
        FeePlansService $feePlansService,
        WidgetService $widgetService,
        PaymentButtonService $paymentButtonService,
        ExcludedCategoriesService $excludedCategoriesService,
        RefundService $refundService,
        InPageService $inPageService,
        AuthenticationSettingsProvider $authenticationSettingsProvider,
        FeePlansProvider $feePlansProvider,
        SettingsRepository $settingsRepository,
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy,
        TranslatorInterface $translator
    ) {
        $this->authenticationService = $authenticationService;
        $this->feePlansService = $feePlansService;
        $this->widgetService = $widgetService;
        $this->paymentButtonService = $paymentButtonService;
        $this->excludedCategoriesService = $excludedCategoriesService;
        $this->refundService = $refundService;
        $this->inPageService = $inPageService;
        $this->authenticationSettingsProvider = $authenticationSettingsProvider;
        $this->feePlansProvider = $feePlansProvider;
        $this->settingsRepository = $settingsRepository;
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
        $this->translator = $translator;
    }

    /**
     * Get the configuration form fields values from POST or DB for put the value on each input.
     * Sometimes we need to get the value only from DB with the param 'getFromDb' if the field is not in the POST,
     * to avoid losing the value when we save the form without changing all fields.
     * And if the field is encrypted, we need to put the obscure value in the input to not show the encrypted value.
     *
     * @param array $languages
     * @return array
     */
    public function getFieldsValue(array $languages = []): array
    {
        $feePlansFieldsValue = $this->feePlansService->fieldsValue();
        $widgetFieldsValue = $this->widgetService->fieldsValueOldWidgetPosition();
        $fieldsValue = $this->authenticationSettingsProvider->getAllFields();

        foreach ($fieldsValue as $field => $param) {
            $fieldsValueByLang = [];
            $fieldsValue[$field] = $this->toolsProxy->getValue($field, $this->configurationRepository->get($field));
            // This function is to get the value from the database if the field is not in the POST.
            if (isset($param['getFromDb']) && $param['getFromDb'] === true) {
                $fieldsValue[$field] = $this->configurationRepository->get($field);
            }

            if (isset($param['encrypted']) && EncryptorHelper::isEncryptionValue($param['encrypted'], $fieldsValue[$field])) {
                $fieldsValue[$field] = EncryptorHelper::OBSCURE_VALUE;
            }

            if (isset($param['options']['lang']) && $param['options']['lang']) {
                foreach ($languages as $lang) {
                    $fieldWithIdLang = $field . '_' . $lang['id_lang'];
                    $fieldsValueByLang[$lang['id_lang']] = $this->toolsProxy->getValue($fieldWithIdLang, $this->configurationRepository->get($fieldWithIdLang));
                }

                $fieldsValue[$field] = $fieldsValueByLang;
            }
        }

        return array_merge(
            $fieldsValue,
            $feePlansFieldsValue,
            $widgetFieldsValue
        );
    }

    /**
     * Get all fields with language fields exploded for each language with the language id at the end of the field name.
     * For example, if we have a field 'title' with 'lang' option and 2 languages with id 1 and 2, we will have 2 fields 'title_1' and 'title_2'.
     *
     * @param array $fields
     * @return array
     */
    public function getSplitLanguageFields(array $fields): array
    {
        return $this->authenticationSettingsProvider->getSplitLanguageFields($fields);
    }

    /**
     * Check validity of the API keys and the equal merchantIds
     * Save the configuration form from all fields values.
     * And return the notification message to show in the configuration form after saving it.
     *
     * @param array $allValuesFromPost
     * @return string
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function saveWithNotification(array $allValuesFromPost): string
    {
        $notificationSuccess = $this->translator->trans('Settings successfully updated', [], 'Modules.Alma.Notifications');
        $overrideValues = [];
        $feePlansFieldsValue = $this->feePlansService->fieldsToSaveFromPost($allValuesFromPost);
        $widgetFieldsValue = $this->widgetService->fieldsValueOldWidgetPosition();

        if ($this->hasNewKey($allValuesFromPost)) {
            $merchantIds = $this->authenticationService->isValidKeys();
            $this->authenticationService->checkSameMerchantIds($merchantIds);
            $mode = $allValuesFromPost[ApiAdminForm::KEY_FIELD_MODE];
            if (!array_key_exists($mode, $merchantIds)) {
                $mode = key($merchantIds);
                $overrideValues[ApiAdminForm::KEY_FIELD_MODE] = $mode;
                $notificationSuccess = $this->translator->trans(
                    'Mode automatically switched to %mode% mode. To use the other mode, please enter the corresponding API key.',
                    ['%mode%' => $mode],
                    'Modules.Alma.Notifications'
                );
            }
            $overrideValues[ApiAdminForm::KEY_FIELD_MERCHANT_ID] = $merchantIds[$mode];
            $feePlanList = $this->feePlansProvider->getFeePlanList();
            $feePlansFieldsValue = $this->feePlansService->fieldsToSaveFromApi($feePlanList);
        }

        $fieldsValue = array_merge(
            $feePlansFieldsValue,
            $this->authenticationSettingsProvider->getSplitLanguageFields(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES)),
            $widgetFieldsValue
        );
        $overrideValues = array_merge(
            $overrideValues,
            $feePlansFieldsValue,
            $this->widgetService->defaultFieldsToSave(),
            $this->paymentButtonService->defaultFieldsToSave(),
            $this->excludedCategoriesService->defaultFieldsToSave(),
            $this->refundService->defaultFieldsToSave(),
            $this->inPageService->defaultFieldsToSave()
        );

        $this->settingsRepository->save(
            $fieldsValue,
            $overrideValues
        );

        return $notificationSuccess;
    }

    /**
     * Check if there is a new API key in the form submission.
     * A new key is detected when the value is not the obscure placeholder and not empty.
     *
     * @param array $allValuesFromPost
     * @return bool
     */
    public function hasNewKey(array $allValuesFromPost): bool
    {
        $testKey = $allValuesFromPost[ApiAdminForm::KEY_FIELD_TEST_API_KEY] ?? null;
        $liveKey = $allValuesFromPost[ApiAdminForm::KEY_FIELD_LIVE_API_KEY] ?? null;

        return $this->isNewKey($testKey) || $this->isNewKey($liveKey);
    }

    /**
     * Check if a key value is a new key (not obscure and not empty).
     *
     * @param string|null $key
     * @return bool
     */
    private function isNewKey(?string $key): bool
    {
        return $key !== null
            && $key !== EncryptorHelper::OBSCURE_VALUE
            && $key !== '';
    }

    /**
     * Check if the configuration is already configured with a merchant id.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MERCHANT_ID);
    }
}
