<?php
/**
 * 2018-2021 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

use Alma;
use Tools;
use Module;
use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AbstractAdminFormBuilder.
 *
 * Builder Form Alma
 */
abstract class AbstractAdminFormBuilder
{
/**
     * Input Switch Form Configuration
     *
     * @return array inputSwitchForm
     */
    public function inputSwitchForm($name, $label, $desc, $helpDesc)
    {
        return [
            'name' => $name,
            // phpcs:ignore
            'label' => $label,
            // phpcs:ignore
            'desc' => $desc,
            'type' => 'switch',
            'values' => [
                'id' => 'id',
                'name' => 'label',
                'query' => [
                    [
                        'id' => 'ON',
                        'val' => true,
                        // PrestaShop won't detect the string if the call to `l` is multiline
                        // phpcs:ignore
                        'label' => $helpDesc,
                    ],
                ],
            ],
        ];
    }

    /**
     * Input Radio Form Configuration
     *
     * @return array inputRadioForm
     */
    public function inputRadioForm($name, $label, $labelOff, $labelOn)
    {
        return [
            'name' => $name,
            'type' => 'radio',
            'label' => $label,
            'class' => 't',
            'required' => true,
            'values' => [
                [
                    'id' => $name . '_OFF',
                    'value' => false,
                    // PrestaShop won't detect the string if the call to `l` is multiline
                    // phpcs:ignore
                    'label' => $labelOff,
                ],
                [
                    'id' => $name . '_ON',
                    'value' => true,
                    // PrestaShop won't detect the string if the call to `l` is multiline
                    // phpcs:ignore
                    'label' => $labelOn,
                ],
            ],
        ];
    }

    /**
     * Input Text Form Configuration
     *
     * @return array inputTextForm
     */
    public function inputTextForm($name, $label, $desc, $placeholder = null)
    {
        $dataInput = [
            'name' => $name,
            'label' => $label,
            'desc' => sprintf(
                // PrestaShop won't detect the string if the call to `l` is multiline
                // phpcs:ignore
                $desc,
                '<b>',
                '</b>'
            ),
            'type' => 'text',
            'size' => 75,
            'required' => false,
        ];

        if ($placeholder) {
            $dataInput['placeholder'] = $placeholder;
        }

        return $dataInput;
    }
}