<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Form\RefundAdminForm;
use PrestaShop\Module\Alma\Tests\Mocks\RefundMock;

class RefundAdminFormTest extends TestCase
{
    public function setup(): void
    {
        $this->formBuilder = $this->createMock(FormBuilder::class);
        $this->feePlansAdminForm = new RefundAdminForm(
            $this->formBuilder,
        );
    }

     /**
      * Test that the build method of RefundAdminForm returns an array with dynamic form.
      */
     public function testFieldsForm()
     {
         $dynamicForm = RefundMock::refundStateSelectExpected();
         $expectedForm = [
             'ALMA_REFUND_ON_CHANGE_HTML' => RefundMock::refundHtmlTemplate('html_content_test'),
             RefundAdminForm::KEY_FIELD_REFUND_ON_CHANGE_STATE => RefundMock::refundStateSwitchExpected(),
             RefundAdminForm::KEY_FIELD_STATE_REFUND_SELECT => RefundMock::refundStateSelectExpected(),
         ];

         $this->assertEquals(
            $expectedForm,
            RefundAdminForm::fieldsForm('html_content_test', $dynamicForm)
         );
     }

     public function tearDown(): void
     {
         $this->formBuilder = null;
     }
}
