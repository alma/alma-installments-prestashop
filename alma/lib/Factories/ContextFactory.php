<?php
/**
 * 2018-2023 Alma SAS.
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

namespace Alma\PrestaShop\Factories;

use Alma\PrestaShop\Exceptions\AlmaException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ContextFactory.
 */
class ContextFactory
{
    /**
     * @return \Context|null
     */
    public function getContext()
    {
        return \Context::getContext();
    }

    /**
     * @return \Link|null
     *
     * @throws AlmaException
     */
    public function getContextLink()
    {
        $context = $this->getContext();

        if (!$context) {
            throw new AlmaException('No context found');
        }

        return $context->link;
    }

    /**
     * @return \Language|\PrestaShopBundle\Install\Language|null
     *
     * @throws AlmaException
     */
    public function getContextLanguage()
    {
        $context = $this->getContext();

        if (!$context) {
            throw new AlmaException('No context found');
        }

        return $context->language;
    }

    /**
     * @return int
     *
     * @throws AlmaException
     */
    public function getContextLanguageId()
    {
        $language = $this->getContextLanguage();

        if (!$language) {
            throw new AlmaException('ContextLanguageId is null');
        }

        return $language->id;
    }

    /**
     * @return \Cart|null
     */
    public function getContextCart()
    {
        $context = $this->getContext();

        return $context->cart;
    }

    /**
     * @return int|null
     */
    public function getContextCartId()
    {
        $contextCart = $this->getContextCart();

        return $contextCart->id;
    }

    /**
     * @return int|null
     */
    public function getContextCartCustomerId()
    {
        $contextCart = $this->getContextCart();

        return $contextCart->id_customer;
    }

    /**
     * @return \Customer|null
     */
    public function getContextCustomer()
    {
        $contextCart = $this->getContext();

        return $contextCart->customer;
    }

    /**
     * @return \Currency|null
     */
    public function getCurrencyFromContext()
    {
        $context = $this->getContext();

        return $context->currency;
    }
}


