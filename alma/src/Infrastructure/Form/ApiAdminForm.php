<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ApiAdminForm extends AbstractAdminForm
{
    public function build(): array
    {
        $inputs = [
            $this->inputFormBuilder->build('select', 'ALMA_MODE', 'Mode', true, [
                'options' => [
                    'query' => [
                        ['id' => 'test', 'name' => 'Test'],
                        ['id' => 'live', 'name' => 'Live'],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ]),
            $this->inputFormBuilder->build('text', 'ALMA_API_KEY_TEST', 'API Key (Test)', false, ['size' => 20]),
            $this->inputFormBuilder->build('text', 'ALMA_API_KEY_LIVE', 'API Key (Live)', false, ['size' => 20])
        ];

        return $this->formBuilder->build('Api Settings', $inputs);
    }
}
