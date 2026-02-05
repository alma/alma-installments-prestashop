<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ApiAdminFormType
{
    public function getForm(): array
    {
        return [
            'form' => [
                'legend' => [
                    'title' => 'Settings',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'Api Key',
                        'name' => 'ALMA_API_KEY',
                        'size' => 20,
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Api Key Live',
                        'name' => 'ALMA_API_KEY_LIVE',
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => 'Save',
                    'class' => 'btn btn-default pull-right',
                ],
            ]
        ];
    }
}
