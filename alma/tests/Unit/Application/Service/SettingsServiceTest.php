<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use Alma\Client\Domain\Entity\FeePlanList;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Application\Provider\AuthenticationSettingsProvider;
use PrestaShop\Module\Alma\Application\Provider\FeePlansProvider;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Application\Service\InPageService;
use PrestaShop\Module\Alma\Application\Service\PaymentButtonService;
use PrestaShop\Module\Alma\Application\Service\RefundService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Application\Service\WidgetService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;
use PrestaShop\Module\Alma\Tests\Mocks\FieldsMock;
use PrestaShopBundle\Translation\TranslatorInterface;

class SettingsServiceTest extends TestCase
{
    /**
     * @var FeePlansService
     */
    private $feePlansService;
    /**
     * @var InPageService
     */
    private $inPageService;
    /**
     * @var PaymentButtonService
     */
    private $paymentButtonService;
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;
    /**
     * @var AuthenticationSettingsProvider
     */
    private $authenticationSettingsProvider;
    /**
     * @var ToolsProxy
     */
    private $toolsProxy;
    /**
     * @var AuthenticationService
     */
    private $authenticationService;
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var FeePlansProvider
     */
    private $feePlansProvider;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function setup(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->feePlansService = $this->createMock(FeePlansService::class);
        $this->widgetService = $this->createMock(WidgetService::class);
        $this->paymentButtonService = $this->createMock(PaymentButtonService::class);
        $this->excludedCategoriesService = $this->createMock(ExcludedCategoriesService::class);
        $this->refundService = $this->createMock(RefundService::class);
        $this->inPageService = $this->createMock(InPageService::class);
        $this->authenticationSettingsProvider = $this->createMock(AuthenticationSettingsProvider::class);
        $this->feePlansProvider = $this->createMock(FeePlansProvider::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->settingsService = new SettingsService(
            $this->authenticationService,
            $this->feePlansService,
            $this->widgetService,
            $this->paymentButtonService,
            $this->excludedCategoriesService,
            $this->refundService,
            $this->inPageService,
            $this->authenticationSettingsProvider,
            $this->feePlansProvider,
            $this->settingsRepository,
            $this->configurationRepository,
            $this->toolsProxy,
            $this->translator
        );
    }

    public function testGetFieldsValue(): void
    {
        $this->feePlansService->expects($this->once())
            ->method('fieldsValue')
            ->willReturn(FeePlansMock::feePlanFieldsExpected(3));
        $this->configurationRepository->expects($this->any())
            ->method('get');
        $this->toolsProxy->expects($this->any())
            ->method('getValue');
        $this->assertIsArray($this->settingsService->getFieldsValue());
    }

    public function testGetFieldsValueWithLang(): void
    {
        $expected = array_merge(
            FieldsMock::fieldsValueWithLang(),
            FieldsMock::fieldValueWithoutLang('classic_field_lang_false', 'value_lang_false_from_repo'),
            FieldsMock::fieldValueWithoutLang('classic_field', 'value_classic_field_from_repo'),
            FeePlansMock::feePlanFieldsValueExpected(3)
        );
        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en', 'language_code' => 'en-us', 'locale' => 'en-US'],
            ['id_lang' => 2, 'iso_code' => 'fr', 'language_code' => 'fr-fr', 'locale' => 'fr-FR']
        ];
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanFromConfiguration')
            ->willReturn(FeePlansMock::almaFeePlanFromDb(3));
        $this->feePlansService->expects($this->once())
            ->method('fieldsValue')
            ->with(FeePlansMock::almaFeePlanFromDb(3))
            ->willReturn(FeePlansMock::feePlanFieldsValueExpected(3));
        $this->authenticationSettingsProvider->expects($this->any())
            ->method('getAllFields')
            ->willReturn(FieldsMock::allFields());
        $this->configurationRepository->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['classic_field_lang_true', 'value_lang_true_from_repo'],
                ['classic_field_lang_true_1', 'value_1_lang_true_from_repo'],
                ['classic_field_lang_true_2', 'value_2_lang_true_from_repo'],
                ['classic_field_lang_false', 'value_lang_false_from_repo'],
                ['classic_field', 'value_classic_field_from_repo'],
                ['ALMA_GENERAL_3_0_0_STATE', '1'],
                ['ALMA_GENERAL_3_0_0_MIN_AMOUNT', '100'],
                ['ALMA_GENERAL_3_0_0_MAX_AMOUNT', '2000'],
                ['ALMA_GENERAL_3_0_0_SORT_ORDER', '1'],
            ]);
        $this->toolsProxy->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['classic_field_lang_true', 'value_lang_true_from_repo', 'value_lang_true_from_repo'],
                ['classic_field_lang_true_1', 'value_1_lang_true_from_repo', 'value_1'],
                ['classic_field_lang_true_2', 'value_2_lang_true_from_repo', 'value_2'],
                ['classic_field_lang_false', 'value_lang_false_from_repo', 'value_lang_false_from_repo'],
                ['classic_field', 'value_classic_field_from_repo', 'value_classic_field_from_repo'],
                ['ALMA_GENERAL_3_0_0_STATE', '1', '1'],
                ['ALMA_GENERAL_3_0_0_MIN_AMOUNT', '100', '100'],
                ['ALMA_GENERAL_3_0_0_MAX_AMOUNT', '2000', '2000'],
                ['ALMA_GENERAL_3_0_0_SORT_ORDER', '1', '1'],
            ]);
        $this->assertEquals($expected, $this->settingsService->getFieldsValue($languages));
    }

    public function testGetAllFieldsWithoutLanguageKeyExploded()
    {
        $allFields = [
            FieldsMock::fieldsWithLangFalse(),
            FieldsMock::fieldsWithoutLang(),
        ];
        $expected = [
            FieldsMock::fieldsWithLangFalse(),
            FieldsMock::fieldsWithoutLang(),
        ];

        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with($allFields)
            ->willReturn($expected);
        $this->assertEquals($expected, $this->settingsService->getSplitLanguageFields($allFields));
    }

    public function testGetAllValuesWithLanguageKeyExploded()
    {
        $allFields = [
            FieldsMock::fieldsWithLangTrue(),
            FieldsMock::fieldsWithoutLang(),
        ];

        $expected = [
            FieldsMock::fieldsWithLangTrueExpected('classic_field_lang_true_1'),
            FieldsMock::fieldsWithLangTrueExpected('classic_field_lang_true_2'),
            FieldsMock::fieldsWithoutLang(),
        ];

        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with($allFields)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->settingsService->getSplitLanguageFields($allFields));
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testSaveWithNotificationWithStringApiKeyExecuteAuthentication()
    {
        $allValuesFromPost = [
            ApiAdminForm::KEY_FIELD_MODE => 'test',
            'ALMA_TEST_API_KEY' => 'test_api_key_post',
            'ALMA_LIVE_API_KEY' => 'live_api_key_post',
        ];
        $merchantIds = [
            'test' => '42',
            'live' => '42'
        ];
        $feePlanP3x = FeePlansMock::feePlan(3);
        $feePlanList = new FeePlanList([$feePlanP3x]);
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Settings successfully updated');
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromPost')
            ->with($allValuesFromPost)
            ->willReturn(FeePlansMock::almaFeePlanForDbExpected(3));
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds');
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->settingsService->saveWithNotification($allValuesFromPost);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testSaveWithNotificationWithObscureApiKeyNotExecuteAuthentication()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
            'ALMA_LIVE_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
        ];
        $overrideValues = [];
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Settings successfully updated');
        $this->authenticationService->expects($this->never())
            ->method('isValidKeys');
        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES))
            ->willReturn(FieldsMock::allFields());
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                FieldsMock::allFields(),
                $overrideValues
            );
        $this->settingsService->saveWithNotification($allValuesFromPost);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsException
     */
    public function testSaveWithNotificationWithObscureAndEmptyApiKeyNotExecuteAuthentication()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
            'ALMA_LIVE_API_KEY' => '',
        ];
        $overrideValues = [];
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Settings successfully updated');
        $this->authenticationService->expects($this->never())
            ->method('isValidKeys');
        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES))
            ->willReturn(FieldsMock::allFields());
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                FieldsMock::allFields(),
                $overrideValues
            );
        $this->settingsService->saveWithNotification($allValuesFromPost);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testDontSaveAuthenticationFailExpectException(): void
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => 'invalid_test_api_key_post',
            'ALMA_LIVE_API_KEY' => 'invalid_live_api_key_post',
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willThrowException(new AuthenticationException());
        $this->expectException(AuthenticationException::class);
        $this->feePlansService->expects($this->never())
            ->method('fieldsValue');
        $this->widgetService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->paymentButtonService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->excludedCategoriesService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->refundService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->inPageService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->settingsRepository->expects($this->never())
            ->method('save');

        $this->settingsService->saveWithNotification($allValuesFromPost);
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testSaveAuthenticationWrongWithDifferentMerchantIds(): void
    {
        $merchantIds = [
            'test' => '42',
            'live' => '43'
        ];
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => 'test_api_key_post_merchant_a',
            'ALMA_LIVE_API_KEY' => 'invalid_live_api_key_post_merchant_b',
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds)
            ->willThrowException(new AuthenticationException());
        $this->feePlansService->expects($this->never())
            ->method('fieldsValue');
        $this->widgetService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->paymentButtonService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->excludedCategoriesService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->refundService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->inPageService->expects($this->never())
            ->method('defaultFieldsToSave');
        $this->settingsRepository->expects($this->never())
            ->method('save');
        $this->expectException(AuthenticationException::class);

        $this->settingsService->saveWithNotification($allValuesFromPost);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testSaveWithOneKeySet(): void
    {
        $merchantIds = [
            'test' => '42'
        ];
        $allValuesFromPost = [
            ApiAdminForm::KEY_FIELD_MODE => 'test',
            'ALMA_TEST_API_KEY' => 'test_api_key_post',
            'ALMA_LIVE_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
        ];
        $feePlanP3x = FeePlansMock::feePlan(3);
        $feePlanList = new FeePlanList([$feePlanP3x]);
        $overrideValues = [
            ApiAdminForm::KEY_FIELD_MERCHANT_ID => '42',
        ];
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Settings successfully updated');
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromPost')
            ->with($allValuesFromPost)
            ->willReturn(FeePlansMock::almaFeePlanForDbExpected(3));
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromApi')
            ->with($feePlanList)
            ->willReturn(FeePlansMock::almaFeePlanForDbExpected(3));
        $this->widgetService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->paymentButtonService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->excludedCategoriesService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->refundService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->inPageService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES))
            ->willReturn(FieldsMock::allFields());
        $fieldsValue = array_merge(
            FeePlansMock::almaFeePlanForDbExpected(3),
            FieldsMock::allFields()
        );
        $overrideValues = array_merge(
            $overrideValues,
            FeePlansMock::almaFeePlanForDbExpected(3)
        );
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                $fieldsValue,
                $overrideValues
            );

        $this->assertEquals(
            'Settings successfully updated',
            $this->settingsService->saveWithNotification($allValuesFromPost)
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testSaveWithOneKeySetAutoSwitch(): void
    {
        $merchantIds = [
            'live' => '42'
        ];
        $allValuesFromPost = [
            ApiAdminForm::KEY_FIELD_MODE => 'test',
            'ALMA_TEST_API_KEY' => '',
            'ALMA_LIVE_API_KEY' => 'test_api_key_post',
        ];
        $feePlanP3x = FeePlansMock::feePlan(3);
        $feePlanList = new FeePlanList([$feePlanP3x]);
        $overrideValues = [
            ApiAdminForm::KEY_FIELD_MERCHANT_ID => '42',
            ApiAdminForm::KEY_FIELD_MODE => 'live',
        ];
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Mode automatically switched to live mode. To use the other mode, please enter the corresponding API key.');
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromPost')
            ->with($allValuesFromPost)
            ->willReturn(FeePlansMock::almaFeePlanForDbExpected(3));
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromApi')
            ->with($feePlanList)
            ->willReturn(FeePlansMock::almaFeePlanForDbExpected(3));
        $this->widgetService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->paymentButtonService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->excludedCategoriesService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->refundService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->inPageService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES))
            ->willReturn(FieldsMock::allFields());
        $fieldsValue = array_merge(
            FeePlansMock::almaFeePlanForDbExpected(3),
            FieldsMock::allFields()
        );
        $overrideValues = array_merge(
            $overrideValues,
            FeePlansMock::almaFeePlanForDbExpected(3)
        );
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                $fieldsValue,
                $overrideValues
            );

        $this->assertEquals(
            'Mode automatically switched to live mode. To use the other mode, please enter the corresponding API key.',
            $this->settingsService->saveWithNotification($allValuesFromPost)
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     */
    public function testSaveWithTwoKeysSet(): void
    {
        $merchantIds = [
            'test' => '42',
            'live' => '42'
        ];
        $allValuesFromPost = [
            ApiAdminForm::KEY_FIELD_MODE => 'test',
            'ALMA_TEST_API_KEY' => 'test_api_key_post',
            'ALMA_LIVE_API_KEY' => 'live_api_key_post',
        ];
        $feePlanP3x = FeePlansMock::feePlan(3);
        $feePlanList = new FeePlanList([$feePlanP3x]);
        $overrideValues = [
            ApiAdminForm::KEY_FIELD_MERCHANT_ID => '42'
        ];
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Settings successfully updated');
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds);
        $this->feePlansProvider->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromApi')
            ->with($feePlanList)
            ->willReturn(FeePlansMock::almaFeePlanForDbExpected(3));
        $this->feePlansService->expects($this->once())
            ->method('fieldsToSaveFromPost')
            ->willReturn(FeePlansMock::feePlanFieldsValueExpected(3));
        $this->widgetService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->paymentButtonService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->excludedCategoriesService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->refundService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->inPageService->expects($this->once())
            ->method('defaultFieldsToSave');
        $this->authenticationSettingsProvider->expects($this->once())
            ->method('getSplitLanguageFields')
            ->with(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES))
            ->willReturn(FieldsMock::allFields());
        $fieldsValue = array_merge(
            FeePlansMock::almaFeePlanForDbExpected(3),
            FieldsMock::allFields()
        );
        $overrideValues = array_merge(
            $overrideValues,
            FeePlansMock::almaFeePlanForDbExpected(3)
        );
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                $fieldsValue,
                $overrideValues
            );

        $this->assertEquals('Settings successfully updated', $this->settingsService->saveWithNotification($allValuesFromPost));
    }

    public function testHasNewKeyWithBothObscureReturnFalse()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
            'ALMA_LIVE_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
        ];
        $this->assertFalse($this->settingsService->hasNewKey($allValuesFromPost));
    }

    public function testHasNewKeyWithOneObscureOneNewKeyReturnTrue()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => 'new_test_api_key',
            'ALMA_LIVE_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
        ];
        $this->assertTrue($this->settingsService->hasNewKey($allValuesFromPost));
    }

    public function testHasNewKeyWithBothNewKeyReturnTrue()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => 'new_test_api_key',
            'ALMA_LIVE_API_KEY' => 'new_live_api_key',
        ];
        $this->assertTrue($this->settingsService->hasNewKey($allValuesFromPost));
    }

    public function testHasNewKeyWithOneNewKeyAndOneEmptyReturnTrue()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => 'new_test_api_key',
            'ALMA_LIVE_API_KEY' => '',
        ];
        $this->assertTrue($this->settingsService->hasNewKey($allValuesFromPost));
    }

    public function testHasNewKeyWithOneObscureAndOneEmptyReturnFalse()
    {
        $allValuesFromPost = [
            'ALMA_TEST_API_KEY' => EncryptorHelper::OBSCURE_VALUE,
            'ALMA_LIVE_API_KEY' => '',
        ];
        $this->assertFalse($this->settingsService->hasNewKey($allValuesFromPost));
    }

    public function testIsConfiguredWithMerchantId()
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('42');
        $this->assertTrue($this->settingsService->isConfigured());
    }

    public function testIsNotConfiguredWithoutMerchantId()
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');
        $this->assertFalse($this->settingsService->isConfigured());
    }
}
