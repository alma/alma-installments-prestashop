<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class SettingsCollectionForm
{
    // You can add here all form classes that you want to include in the settings collection
    public const SETTINGS_FORMS_CLASSES = [
        ApiAdminForm::class
    ];

    /**
     * Get all fields from registered forms
     * @param array $formClasses Array of form class names to retrieve fields from
     * @return array
     */
    public function getAllFields(array $formClasses): array
    {
        $allFields = [];

        foreach ($formClasses as $formClass) {
            if (defined("{$formClass}::FIELDS_FORM")) {
                $allFields = array_merge($allFields, constant("{$formClass}::FIELDS_FORM"));
            }
        }

        return $allFields;
    }
}
