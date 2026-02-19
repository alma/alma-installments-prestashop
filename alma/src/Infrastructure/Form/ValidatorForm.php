<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use Validate;

class ValidatorForm
{
    /**
     * Validate the legacy configuration form fields.
     * Check input text fields for being non-empty and containing only valid characters (using Validate::isGenericName).
     * If a field is not required, it will be skipped in validation even if it's empty.
     *
     * @param array $fieldsForm The fields to validate with their parameters (type, required, etc.)
     * @param array $allValues The values submitted from the form, indexed by field name
     *
     * @return array an array of error messages if validation fails, or an empty array if validation passes
     */
    public static function legacyValidate(array $fieldsForm, array $allValues): array
    {
        $errors = [];
        foreach ($fieldsForm as $field => $params) {
            if ($params['required'] === false) {
                continue;
            }

            if (empty($allValues[$field]) || !Validate::isGenericName($allValues[$field])) {
                $errors[] = sprintf('Invalid Configuration value for %s', $field);
            }
        }

        return $errors;
    }
}
