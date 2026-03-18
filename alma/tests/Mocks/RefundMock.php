<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

final class RefundMock
{
    public static function refundHtmlTemplate($templateHtml = ''): array
    {
        return [
            'type' => 'html',
            'label' => '',
            'required' => false,
            'form' => 'refund_on_change',
            'options' => [
                'col' => 12,
                'html_content' => $templateHtml,
            ],
        ];
    }

    public static function refundStateSwitchExpected(): array
    {
        return [
            'type' => 'switch',
            'label' => 'Activate refund by change state',
            'required' => false,
            'form' => 'refund_on_change',
            'encrypted' => false,
            'options' => [
                'values' => [
                    [
                        'id' => 'ENABLE',
                        'value' => 1,
                        'label' => 'Enabled',
                    ],
                    [
                        'id' => 'DISABLE',
                        'value' => 0,
                        'label' => 'Disabled'
                    ]
                ],
            ],
        ];
    }

    public static function refundStateSelectExpected(): array
    {
        return [
            'type' => 'select',
            'label' => 'Select the state',
            'required' => false,
            'form' => 'refund_on_change',
            'encrypted' => false,
            'options' => [
                'desc' => 'Description of the select',
                'query' => [
                    [
                        'id_order_state' => 1,
                        'name' => 'State 1',
                    ],
                    [
                        'id_order_state' => 2,
                        'name' => 'State 2',
                    ],
                ],
                'id' => 'id_order_state',
                'name' => 'name',
            ],
        ];
    }
}
