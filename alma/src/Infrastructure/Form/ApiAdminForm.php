<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ApiAdminForm extends AbstractAdminForm
{
    const TITLE = 'API Settings';
    public const FIELDS_FORM = [
        'ALMA_MODE' => [
            'type' => 'select',
            'label' => 'Mode',
            'required' => true,
            'form' => 'api',
            'options' => [
                'options' => [
                    'query' => [
                        ['id' => 'test', 'name' => 'Test'],
                        ['id' => 'live', 'name' => 'Live'],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ]
        ],
        'ALMA_API_KEY_TEST' => [
            'type' => 'text',
            'label' => 'API Key (Test)',
            'required' => false,
            'form' => 'api',
            'options' => [
                'size' => 20,
            ],
        ],
        'ALMA_API_KEY_LIVE' => [
            'type' => 'text',
            'label' => 'API Key (Live)',
            'required' => false,
            'form' => 'api',
            'options' => [
                'size' => 20,
            ],
        ],
    ];
}
