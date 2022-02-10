<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Forms;

use Alma\PrestaShop\Utils\Settings;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ExcludedCategoryAdminFormBuilder
 */
class ExcludedCategoryAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_CATEGORIES_WDGT_NOT_ELGBL = 'ALMA_CATEGORIES_WDGT_NOT_ELGBL';
    const ALMA_NOT_ELIGIBLE_CATEGORIES = 'ALMA_NOT_ELIGIBLE_CATEGORIES';

    protected function configForm()
    {
        // Exclusion
        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/excludedCategories.tpl"
        );

        $excludedCategoryNames = Settings::getExcludedCategoryNames();

        $tpl->assign([
            'excludedCategories' => count($excludedCategoryNames) > 0
                ? implode(', ', $excludedCategoryNames)
                : $this->module->l('No excluded categories', 'ExcludedCategoryAdminFormBuilder'),
            'excludedLink' => $this->context->link->getAdminLink('AdminAlmaCategories'),
        ]);

        return [
            $this->inputHtml($tpl),
            $this->inputAlmaSwitchForm(
                self::ALMA_CATEGORIES_WDGT_NOT_ELGBL,
                $this->module->l('Display message', 'ExcludedCategoryAdminFormBuilder'),
                // phpcs:ignore
                $this->module->l('Display the message below if the product is excluded from the category', 'ExcludedCategoryAdminFormBuilder'),
                // phpcs:ignore
                $this->module->l('Display the message below if the product is excluded', 'ExcludedCategoryAdminFormBuilder')
            ),
            $this->inputTextForm(
                self::ALMA_NOT_ELIGIBLE_CATEGORIES,
                $this->module->l('Excluded categories non-eligibility message ', 'ExcludedCategoryAdminFormBuilder'),
                // phpcs:ignore
                $this->module->l('Message displayed on an excluded product page or on the cart page if it contains an excluded product.', 'ExcludedCategoryAdminFormBuilder'),
                null,
                false,
                true
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Excluded categories', 'ExcludedCategoryAdminFormBuilder');
    }
}
