<?php
/**
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Forms;

use Alma\PrestaShop\Utils\ApiKeyHelper;

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
    private $image;
    private $title;

    /**
     * @param $image
     * @param $title
     */
    public function __construct($image, $title)
    {
        $this->image = $image;
        $this->title = $title;
    }

    /**
     * Form Configuration
     *
     * @return array built form
     */
    public function build()
    {
        return [
            'form' => [
                'legend' => $this->legendForm(),
                'input' => $this->configForm(),
                'submit' => ['title' => $this->getSubmitTitle(), 'class' => 'button btn btn-default pull-right'],
            ],
        ];
    }

    /**
     * Input Switch Form Configuration
     *
     * @return array inputSwitchForm
     */
    protected function inputSwitchForm($name, $label, $labelOn = 'Yes', $labelOff = 'No', $desc = null, $form_group_class = null)
    {
        $dataInput = [
            'name' => $name,
            'label' => $label,
            'type' => 'switch',
            'required' => false,
            'values' => [
                [
                    'id' => $name . '_ON',
                    'value' => 1,
                    'label' => $labelOn,
                ],
                [
                    'id' => $name . '_OFF',
                    'value' => 0,
                    'label' => $labelOff,
                ],
            ],
        ];

        if ($form_group_class) {
            $dataInput['form_group_class'] = $form_group_class;
        }

        if ($desc) {
            $dataInput['desc'] = $desc;
        }

        return $dataInput;
    }

    /**
     * Input Switch Form Configuration
     *
     * @return array inputSwitchForm
     */
    protected function inputAlmaSwitchForm($name, $label, $desc = null, $helpDesc = null, $form_group_class = null, $readonly = false)
    {
        $dataInput = [
            'name' => $name,
            'label' => $label,
            'type' => 'alma_switch',
            'readonly' => $readonly,
            'values' => [
                'id' => 'id',
                'name' => 'label',
                'query' => [
                    [
                        'id' => 'ON',
                        'val' => true,
                        'label' => '',
                    ],
                ],
            ],
        ];

        if ($form_group_class) {
            $dataInput['form_group_class'] = $form_group_class;
        }

        if ($desc) {
            $dataInput['desc'] = $desc;
        }
        if ($helpDesc) {
            $dataInput['values']['query'][0]['label'] = $helpDesc;
        }

        return $dataInput;
    }

    /**
     * Input Radio Form Configuration
     *
     * @return array inputRadioForm
     */
    protected function inputRadioForm($name, $label, $labelOff, $labelOn)
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
                    'label' => $labelOff,
                ],
                [
                    'id' => $name . '_ON',
                    'value' => true,
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
    protected function inputTextForm($name, $label, $desc = null, $placeholder = null, $required = false, $lang = false)
    {
        $dataInput = [
            'name' => $name,
            'label' => $label,
            'type' => 'text',
            'size' => 75,
            'required' => $required,
        ];

        if ($desc) {
            $dataInput['desc'] = $desc;
        }
        if ($placeholder) {
            $dataInput['placeholder'] = $placeholder;
        }
        if ($lang) {
            $dataInput['lang'] = $lang;
        }

        return $dataInput;
    }

    /**
     * Input Text Form Configuration
     *
     * @return array inputTextForm
     */
    protected function inputSecretForm($name, $label, $desc = null, $placeholder = ApiKeyHelper::OBCUR_VALUE, $required = false, $lang = false)
    {
        $dataInput = [
            'name' => $name,
            'label' => $label,
            'type' => 'secret',
            'size' => 75,
            'required' => $required,
        ];

        if ($desc) {
            $dataInput['desc'] = $desc;
        }
        if ($placeholder) {
            $dataInput['placeholder'] = $placeholder;
        }
        if ($lang) {
            $dataInput['lang'] = $lang;
        }

        return $dataInput;
    }

    /**
     * Input Number Form Configuration
     *
     * @return array inputNumberForm
     */
    protected function inputNumberForm($name, $label, $desc, $min = null, $max = null, $form_group_class = null, $readonly = false)
    {
        $dataInput = [
            'name' => $name,
            'label' => $label,
            'desc' => $desc,
            'type' => 'number',
            'readonly' => $readonly,
        ];

        if ($form_group_class) {
            $dataInput['form_group_class'] = $form_group_class;
        }

        if ($min) {
            $dataInput['min'] = $min;
        }
        if ($max) {
            $dataInput['max'] = $max;
        }

        return $dataInput;
    }

    /**
     * Input Select Form Configuration
     *
     * @return array inputSelectForm
     */
    protected function inputSelectForm($name, $label, $desc, $query, $id)
    {
        return [
            'name' => $name,
            'label' => $label,
            'desc' => $desc,
            'type' => 'select',
            'required' => true,
            'options' => [
                'query' => $query,
                'id' => $id,
                'name' => 'name',
            ],
        ];
    }

    /**
     * Input Html Configuration
     *
     * @return array inputHtml
     */
    protected function inputHtml($tpl = null, $htmlContent = null, $form_group_class = null)
    {
        $dataInput = [
            'name' => null,
            'label' => false,
            'type' => 'html',
            'form_group_class' => 'input_html',
            'col' => 12,
        ];

        if ($htmlContent) {
            $dataInput['html_content'] = $htmlContent;
        }

        if ($tpl) {
            $dataInput['desc'] = $tpl->fetch();
        }

        if ($form_group_class) {
            $dataInput['form_group_class'] = $form_group_class;
        }

        return $dataInput;
    }

    /**
     * Input Hidden Configuration
     *
     * @return array inputHiddenForm
     */
    protected function inputHiddenForm($name, $form_group_class = null)
    {
        $dataInput = [
            'name' => $name,
            'label' => null,
            'type' => 'hidden',
        ];

        if ($form_group_class) {
            $dataInput['form_group_class'] = $form_group_class;
        }

        return $dataInput;
    }

    /**
     * Legend Form Configuration
     *
     * @return array legendForm
     */
    protected function legendForm()
    {
        return [
            'title' => $this->title,
            'image' => $this->image,
        ];
    }

    /**
     * @return array
     */
    abstract protected function configForm();

    /**
     * @return string
     */
    abstract protected function getSubmitTitle();
}
