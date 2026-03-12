<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use stdClass;

class SettingsCollectionFormTest extends TestCase
{
    public function testGetAllFieldsWithOneClassWithoutFieldForm()
    {
        $classes = array_merge(FormCollection::SETTINGS_FORMS_CLASSES, [stdClass::class]);
        $this->assertEquals(
            array_merge(
                ApiAdminForm::fieldsForm(),
                FeePlansAdminForm::fieldsForm()
            ),
            FormCollection::getAllFields($classes)
        );
    }

    public function testGetAllFields()
    {
        $this->assertEquals(
            array_merge(
                ApiAdminForm::fieldsForm(),
                FeePlansAdminForm::fieldsForm()
            ),
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES)
        );
    }
}
