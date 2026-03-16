<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class FeePlansAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_FEE_PLAN_LIST = 'ALMA_FEE_PLAN_LIST';
    public const KEY_FIELD_FEE_PLAN_STATE = 'ALMA_%s_STATE';
    public const KEY_FIELD_FEE_PLAN_MIN_AMOUNT = 'ALMA_%s_MIN_AMOUNT';
    public const KEY_FIELD_FEE_PLAN_MAX_AMOUNT = 'ALMA_%s_MAX_AMOUNT';
    public const KEY_FIELD_FEE_PLAN_SORT_ORDER = 'ALMA_%s_SORT_ORDER';

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('Installments plans', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $inputs = [
            'ALMA_TABS' => [
                'type' => 'html',
                'label' => '',
                'required' => false,
                'form' => 'fee_plans',
                'options' => [
                    'col' => 12,
                    'html_content' => $templateHtml,
                ],
            ]
        ];

        return array_merge($inputs, $dynamicForm);
    }
}
