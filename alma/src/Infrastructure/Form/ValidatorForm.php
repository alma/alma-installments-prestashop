<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
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
            if (!isset($params['required']) || $params['required'] === false) {
                continue;
            }

            if (empty($allValues[$field]) || !Validate::isGenericName($allValues[$field])) {
                $errors[] = sprintf('Invalid Configuration value for %s', $field);
            }
        }

        return $errors;
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public static function checkLimitAmountPlan(FeePlan $feePlan, int $minAmount, int $maxAmount): void
    {
        if ($minAmount < $feePlan->getMinPurchaseAmount()) {
            throw new FeePlansException('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        }

        if ($maxAmount > $feePlan->getMaxPurchaseAmount()) {
            throw new FeePlansException('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        }

        if ($minAmount > $feePlan->getMaxPurchaseAmount()) {
            throw new FeePlansException('The minimum purchase amount cannot be higher than the maximum.');
        }

        if ($maxAmount < $feePlan->getMinPurchaseAmount()) {
            throw new FeePlansException('The maximum purchase amount cannot be lower than the minimum.');
        }
    }
}
