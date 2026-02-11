<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Form\InputFormBuilder;
use PrestaShop\Module\Alma\Tests\Mocks\FormExpectedMother;
use PrestaShop\Module\Alma\Tests\Mocks\InputExpectedMother;

class ApiAdminFormTest extends TestCase
{
    /**
     * @var InputFormBuilder
     */
    private $inputFormBuilder;

    public function setup(): void
    {
        $this->formBuilder = $this->createMock(FormBuilder::class);
        $this->inputFormBuilder = $this->createMock(InputFormBuilder::class);
        $this->apiAdminForm = new ApiAdminForm(
            $this->formBuilder,
            $this->inputFormBuilder
        );
    }

     /**
      * Test that the build method of ApiAdminForm returns an array with a 'form' key.
      * TODO: Do we want to test the exact structure of the form array?
      */
     public function testBuildIfReturnAnArrayWithKeyForm()
     {
         $this->inputFormBuilder
             ->method('build')
             ->willReturn(InputExpectedMother::text());
         $this->formBuilder->expects($this->once())
             ->method('build')
             ->willReturn(FormExpectedMother::form());
         $this->assertArrayHasKey('form', $this->apiAdminForm->build());
     }

     public function tearDown(): void
     {
         $this->formBuilder = null;
         $this->inputFormBuilder = null;
     }
}
