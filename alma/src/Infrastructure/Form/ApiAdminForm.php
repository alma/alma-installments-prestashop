<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class ApiAdminForm extends AbstractAdminForm
{
    public static function title(): string
    {
        return 'API configuration';
    }

    public static function fieldsForm(): array
    {
        return [
            'ALMA_API_MODE' => [
                'type' => 'select',
                'label' => 'API Mode',
                'required' => true,
                'form' => 'api',
                'encrypted' => false,
                'options' => [
                    'options' => [
                        'query' => [
                            ['id' => 'test', 'name' => 'Test'],
                            ['id' => 'live', 'name' => 'Live'],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => 'Use Test mode until you are ready to take real orders with Alma. In Test mode, only admins can see Alma on cart/checkout pages.'
                ]
            ],
            'ALMA_LIVE_API_KEY' => [
                'type' => 'text',
                'label' => 'Live API key',
                'required' => false,
                'form' => 'api',
                'encrypted' => true,
                'options' => [
                    'size' => 20,
                    'desc' => sprintf(
                        'Not required for Test mode – You can find your Live API key on %1$syour Alma dashboard%2$s',
                        '<a href="https://dashboard.getalma.eu/api" target="_blank">',
                        '</a>'
                    ),
                ],
            ],
            'ALMA_TEST_API_KEY' => [
                'type' => 'text',
                'label' => 'Test API key',
                'required' => false,
                'form' => 'api',
                'encrypted' => true,
                'options' => [
                    'size' => 20,
                    'desc' => sprintf(
                        'Not required for Live mode – You can find your Test API key on %1$syour sandbox dashboard%2$s',
                        '<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">',
                        '</a>'
                    ),
                ],
            ],
        ];
    }
}
