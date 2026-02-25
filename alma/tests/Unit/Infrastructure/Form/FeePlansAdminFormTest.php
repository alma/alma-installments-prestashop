<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class FeePlansAdminFormTest extends TestCase
{
    public function setup(): void
    {
        $this->formBuilder = $this->createMock(FormBuilder::class);
        $this->feePlansAdminForm = new FeePlansAdminForm(
            $this->formBuilder,
        );
    }

     /**
      * Test that the build method of ApiAdminForm returns an array with a 'form' key.
      * TODO: Do we want to test the exact structure of the form array?
      */
     public function testFieldsForm()
     {
         $dynamicForm = FeePlansMock::feePlanFieldsExpected(3);
         $expectedForm = array_merge(FeePlansMock::feePlanHtmlTemplate('html_content_test'), FeePlansMock::feePlanFieldsExpected(3));
            $this->assertEquals(
                $expectedForm,
                FeePlansAdminForm::fieldsForm('html_content_test', $dynamicForm)
            );
     }

     public function tearDown(): void
     {
         $this->formBuilder = null;
     }
}
