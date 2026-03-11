<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\PaymentButtonService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

class PaymentButtonServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->paymentButtonService = new PaymentButtonService(
            $this->context,
            $this->configurationRepository,
            $this->languageRepository
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
            ['id_lang' => 1, 'iso_code' => 'en']
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

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
            ['id_lang' => 1, 'iso_code' => 'en'],
            ['id_lang' => 2, 'iso_code' => 'fr'],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->assertEquals($expected, $this->paymentButtonService->defaultFieldsToSave());
    }
}
