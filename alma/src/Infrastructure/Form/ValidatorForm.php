<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use Alma\Client\Domain\Entity\FeePlan;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShopBundle\Translation\TranslatorInterface;
use Validate;

class ValidatorForm
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
    public static function legacyValidate(array $fieldsForm, array $allValues, array $languages = []): array
    {
        $errors = [];
        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en'],
            ['id_lang' => 2, 'iso_code' => 'fr'],
        ];
        foreach ($fieldsForm as $field => $params) {
            if (!isset($params['required']) || $params['required'] === false) {
                continue;
            }

            if (isset($params['lang']) && $params['lang']) {
                foreach ($languages as $language) {
                    $fieldLanguage = $field . '_' . $language['id_lang'];
                    if (empty($allValues[$fieldLanguage]) || !Validate::isGenericName($allValues[$fieldLanguage])) {
                        $errors[] = sprintf('Invalid Configuration value for %s', $fieldLanguage);
                    }
                }

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
    public function checkLimitAmountPlan(FeePlan $feePlan, int $minAmount, int $maxAmount): void
    {
        if ($minAmount < $feePlan->getMinPurchaseAmount()) {
            $message = $this->translator->trans('The minimum purchase amount cannot be lower than the minimum allowed by Alma.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }

        if ($maxAmount > $feePlan->getMaxPurchaseAmount()) {
            $message = $this->translator->trans('The maximum purchase amount cannot be higher than the maximum allowed by Alma.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }

        if ($minAmount > $feePlan->getMaxPurchaseAmount()) {
            $message = $this->translator->trans('The minimum purchase amount cannot be higher than the maximum.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }

        if ($maxAmount < $feePlan->getMinPurchaseAmount()) {
            $message = $this->translator->trans('The maximum purchase amount cannot be lower than the minimum.', [], 'Modules.Alma.Notifications');
            throw new FeePlansException($message);
        }
    }
}
