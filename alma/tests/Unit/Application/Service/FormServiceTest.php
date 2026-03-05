<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Application\Service\FormService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class FormServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->feePlansService = $this->createMock(FeePlansService::class);
        $this->apiAdminForm = $this->createMock(ApiAdminForm::class);
        $this->feePlansAdminForm = $this->createMock(FeePlansAdminForm::class);
        $this->productWidgetAdminForm = $this->createMock(ProductWidgetAdminForm::class);
        $this->cartWidgetAdminForm = $this->createMock(CartWidgetAdminForm::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->formService = new FormService(
            $this->feePlansService,
            $this->apiAdminForm,
            $this->feePlansAdminForm,
            $this->productWidgetAdminForm,
            $this->cartWidgetAdminForm,
            $this->configurationRepository
        );
    }

    public function testGetFormWithoutMerchantId()
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->apiAdminForm->expects($this->once())
            ->method('build')
            ->willReturn(['api_form']);

        $form = $this->formService->getForm();

        $this->assertCount(1, $form);
        $this->assertEquals([
            ['api_form']
        ], $form);
    }

    public function testGetFormWithMerchantId()
    {
        $this->apiAdminForm->expects($this->once())
            ->method('build')
            ->willReturn(['api_form']);

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');

        $this->feePlansService->expects($this->once())
            ->method('createTemplateTabs')
            ->willReturn('template_tabs');

        $this->feePlansService->expects($this->once())
            ->method('feePlansFields')
            ->willReturn(['fee_plans_fields']);

        $this->feePlansAdminForm->expects($this->once())
            ->method('build')
            ->with('template_tabs', ['fee_plans_fields'])
            ->willReturn(['fee_plans_form']);

        $this->productWidgetAdminForm->expects($this->once())
            ->method('build')
            ->willReturn(['product_widget_form']);

        $this->cartWidgetAdminForm->expects($this->once())
            ->method('build')
            ->willReturn(['cart_widget_form']);

        $form = $this->formService->getForm();

        $this->assertCount(4, $form);
        $this->assertEquals([
            ['fee_plans_form'],
            ['product_widget_form'],
            ['cart_widget_form'],
            ['api_form'],
        ], $form);
    }
}
