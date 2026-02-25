<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class FeePlansAdminForm extends AbstractAdminForm
{
    public static function title(): string
    {
        return 'Installments plans';
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
                    'html_content' => $templateHtml,
                ],
            ]
        ];

        return array_merge($inputs, $dynamicForm);
    }
}
