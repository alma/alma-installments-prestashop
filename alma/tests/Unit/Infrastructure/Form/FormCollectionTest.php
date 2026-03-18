<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\DebugAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Form\InPageAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\PaymentButtonAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\RefundAdminForm;
use stdClass;

class FormCollectionTest extends TestCase
{
    public function testGetAllFieldsWithOneClassWithoutFieldForm()
    {
        $classes = array_merge(FormCollection::SETTINGS_FORMS_CLASSES, [stdClass::class]);
        $this->assertEquals(
            array_merge(
                ApiAdminForm::fieldsForm(),
                FeePlansAdminForm::fieldsForm(),
                ProductWidgetAdminForm::fieldsForm(),
                CartWidgetAdminForm::fieldsForm(),
                PaymentButtonAdminForm::fieldsForm(),
                ExcludedCategoriesAdminForm::fieldsForm(),
                RefundAdminForm::fieldsForm(),
                InPageAdminForm::fieldsForm(),
                DebugAdminForm::fieldsForm()
            ),
            FormCollection::getAllFields($classes)
        );
    }

    public function testGetAllFields()
    {
        $this->assertEquals(
            array_merge(
                ApiAdminForm::fieldsForm(),
                FeePlansAdminForm::fieldsForm(),
                ProductWidgetAdminForm::fieldsForm(),
                CartWidgetAdminForm::fieldsForm(),
                PaymentButtonAdminForm::fieldsForm(),
                ExcludedCategoriesAdminForm::fieldsForm(),
                RefundAdminForm::fieldsForm(),
                InPageAdminForm::fieldsForm(),
                DebugAdminForm::fieldsForm()
            ),
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES)
        );
    }

    public function testGetAllFieldsBeforeApiKeySaved()
    {
        $this->assertEquals(
            array_merge(
                ApiAdminForm::fieldsForm(),
                DebugAdminForm::fieldsForm()
            ),
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES_BEFORE_AUTH)
        );
    }
}
