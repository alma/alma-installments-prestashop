<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\PaymentButtonService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

class PaymentButtonServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->paymentButtonService = new PaymentButtonService(
            $this->context,
            $this->configurationRepository,
            $this->languageRepository,
            $this->translator
        );
    }

    public function testDefaultFieldsToSaveFirstSaveWithOneLanguageEn(): void
    {
        $expected = [
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE . '_1' => 'Pay now by credit card',
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC . '_1' => 'Fast and secure payments.',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE . '_1' => 'Pay in %d installments',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC . '_1' => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE . '_1' => 'Buy now Pay in %d days',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC . '_1' => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE . '_1' => 'Pay in %d installments',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC . '_1' => 'Fast and secure payment by credit card.',
        ];

        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en', 'locale' => 'en-US'],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnOnConsecutiveCalls(
                'Pay now by credit card',
                'Fast and secure payments.',
                'Pay in %d installments',
                'Fast and secure payment by credit card.',
                'Buy now Pay in %d days',
                'Fast and secure payment by credit card.',
                'Pay in %d installments',
                'Fast and secure payment by credit card.',
            );

        $this->assertEquals($expected, $this->paymentButtonService->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveFirstSaveWithLanguagesEnAndFr(): void
    {
        $expected = [
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE . '_1' => 'Pay now by credit card',
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_TITLE . '_2' => 'Payer maintenant par carte bancaire',
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC . '_1' => 'Fast and secure payments.',
            PaymentButtonAdminForm::KEY_FIELD_PAYNOW_BUTTON_DESC . '_2' => 'Paiement rapide et sécurisé.',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE . '_1' => 'Pay in %d installments',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_TITLE . '_2' => 'Payer en %d fois',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC . '_1' => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_PNX_BUTTON_DESC . '_2' => 'Paiement rapide et sécurisé, par carte bancaire.',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE . '_1' => 'Buy now Pay in %d days',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_TITLE . '_2' => 'Payer dans %d jours',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC . '_1' => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_PAYLATER_BUTTON_DESC . '_2' => 'Paiement rapide et sécurisé, par carte bancaire.',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE . '_1' => 'Pay in %d installments',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_TITLE . '_2' => 'Payer en %d fois',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC . '_1' => 'Fast and secure payment by credit card.',
            PaymentButtonAdminForm::KEY_FIELD_CREDIT_BUTTON_DESC . '_2' => 'Paiement rapide et sécurisé, par carte bancaire.',
        ];

        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en', 'locale' => 'en-US'],
            ['id_lang' => 2, 'iso_code' => 'fr', 'locale' => 'fr-FR'],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnOnConsecutiveCalls(
                'Pay now by credit card',
                'Fast and secure payments.',
                'Pay in %d installments',
                'Fast and secure payment by credit card.',
                'Buy now Pay in %d days',
                'Fast and secure payment by credit card.',
                'Pay in %d installments',
                'Fast and secure payment by credit card.',
                'Payer maintenant par carte bancaire',
                'Paiement rapide et sécurisé.',
                'Payer en %d fois',
                'Paiement rapide et sécurisé, par carte bancaire.',
                'Payer dans %d jours',
                'Paiement rapide et sécurisé, par carte bancaire.',
                'Payer en %d fois',
                'Paiement rapide et sécurisé, par carte bancaire.',
            );

        $this->assertEquals($expected, $this->paymentButtonService->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveUpdateValues()
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');
        $this->assertEquals([], $this->paymentButtonService->defaultFieldsToSave());
    }
}
