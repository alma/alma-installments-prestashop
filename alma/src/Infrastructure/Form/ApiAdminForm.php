<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use Alma\Client\Domain\ValueObject\Environment;

class ApiAdminForm extends AbstractAdminForm
{
    public const KEY_FIELD_MODE = 'ALMA_API_MODE';
    public const KEY_FIELD_MERCHANT_ID = 'ALMA_MERCHANT_ID';
    public const KEY_FIELD_LIVE_API_KEY = 'ALMA_LIVE_API_KEY';
    public const KEY_FIELD_TEST_API_KEY = 'ALMA_TEST_API_KEY';
    public const KEY_FIELDS_API_KEYS = [
        Environment::TEST_MODE => self::KEY_FIELD_TEST_API_KEY,
        Environment::LIVE_MODE => self::KEY_FIELD_LIVE_API_KEY,
    ];

    public static function title(): string
    {
        $translator = \Context::getContext()->getTranslator();
        return $translator->trans('API configuration', [], 'Modules.Alma.Settings');
    }

    public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array
    {
        $translator = \Context::getContext()->getTranslator();
        return [
            self::KEY_FIELD_MODE => [
                'type' => 'select',
                'label' => $translator->trans('API Mode', [], 'Modules.Alma.Settings'),
                'required' => true,
                'form' => 'api',
                'encrypted' => false,
                'options' => [
                    'options' => [
                        'query' => [
                            ['id' => Environment::TEST_MODE, 'name' => 'Test'],
                            ['id' => Environment::LIVE_MODE, 'name' => 'Live'],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'desc' => $translator->trans(
                        'Use Test mode until you are ready to take real orders with Alma. In Test mode, only admins can see Alma on cart/checkout pages.',
                        [],
                        'Modules.Alma.Settings'
                    ),
                ]
            ],
            self::KEY_FIELD_LIVE_API_KEY => [
                'type' => 'text',
                'label' => $translator->trans('Live API key', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'api',
                'encrypted' => true,
                'options' => [
                    'size' => 20,
                    'desc' => sprintf(
                        $translator->trans(
                            'Not required for Test mode – You can find your Live API key on %1$syour Alma dashboard%2$s',
                            [],
                            'Modules.Alma.Settings'
                        ),
                        '<a href="https://dashboard.getalma.eu/api" target="_blank">',
                        '</a>'
                    ),
                ],
            ],
            self::KEY_FIELD_TEST_API_KEY => [
                'type' => 'text',
                'label' => $translator->trans('Test API key', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'api',
                'encrypted' => true,
                'options' => [
                    'size' => 20,
                    'desc' => sprintf(
                        $translator->trans(
                            'Not required for Live mode – You can find your Test API key on %1$syour sandbox dashboard%2$s',
                            [],
                            'Modules.Alma.Settings'
                        ),
                        '<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">',
                        '</a>'
                    ),
                ],
            ],
            self::KEY_FIELD_MERCHANT_ID => [
                'type' => 'text',
                'label' => $translator->trans('Merchant ID', [], 'Modules.Alma.Settings'),
                'required' => false,
                'form' => 'api',
                'encrypted' => false,
                'getFromDb' => true,
                'options' => [
                    'size' => 20,
                    'readonly' => true,
                ],
            ],
        ];
    }
}
