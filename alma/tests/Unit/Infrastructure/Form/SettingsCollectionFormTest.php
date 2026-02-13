<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsCollectionForm;
use stdClass;

class SettingsCollectionFormTest extends TestCase
{
    /**
     * @var SettingsCollectionForm
     */
    private SettingsCollectionForm $settingsCollectionForm;

    public function setUp(): void
    {
        $this->settingsCollectionForm = new SettingsCollectionForm();
    }

    public function testGetAllFieldsWithOneClassWithoutFieldForm()
    {
        $classes = array_merge(SettingsCollectionForm::SETTINGS_FORMS_CLASSES, [stdClass::class]);
        $this->assertEquals(
            array_merge(
                ApiAdminForm::FIELDS_FORM
            ),
            $this->settingsCollectionForm->getAllFields($classes)
        );
    }

    public function testGetAllFields()
    {
        $this->assertEquals(
            array_merge(
                ApiAdminForm::FIELDS_FORM
            ),
            $this->settingsCollectionForm->getAllFields(SettingsCollectionForm::SETTINGS_FORMS_CLASSES)
        );
    }
}
