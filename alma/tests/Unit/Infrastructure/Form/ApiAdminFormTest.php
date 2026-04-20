<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Tests\Mocks\FormExpectedMock;

class ApiAdminFormTest extends TestCase
{
    public function setup(): void
    {
        $this->formBuilder = $this->createMock(FormBuilder::class);
        $this->apiAdminForm = new ApiAdminForm(
            $this->formBuilder
        );
    }

     /**
      * Test that the build method of ApiAdminForm returns an array with a 'form' key.
      * TODO: Do we want to test the exact structure of the form array?
      */
     public function testBuildIfReturnAnArrayWithKeyForm()
     {
         $this->formBuilder->expects($this->once())
             ->method('build')
             ->willReturn(FormExpectedMock::form());
         $this->assertArrayHasKey('form', $this->apiAdminForm->build());
     }

     public function tearDown(): void
     {
         $this->formBuilder = null;
     }
}
