<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Builders\Helpers\ShareOfCheckoutHelperBuilder;
use Alma\PrestaShop\Exceptions\ShareOfCheckoutException;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\ShareOfCheckoutHelper;
use Alma\PrestaShop\Model\AlmaApiKeyModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShareOfCheckoutService
{
    /**
     * @var ShareOfCheckoutHelper
     */
    private $shareOfCheckoutHelper;
    /**
     * @var AlmaApiKeyModel
     */
    private $almaApiKeyModel;

    /**
     * @param ShareOfCheckoutHelper $shareOfCheckoutHelper
     * @param AlmaApiKeyModel $almaApiKeyModel
     */
    public function __construct(
        $shareOfCheckoutHelper = null,
        $almaApiKeyModel = null
    ) {
        if (!$shareOfCheckoutHelper) {
            $shareOfCheckoutHelper = (new ShareOfCheckoutHelperBuilder())->getInstance();
        }
        $this->shareOfCheckoutHelper = $shareOfCheckoutHelper;
        if (!$almaApiKeyModel) {
            $almaApiKeyModel = new AlmaApiKeyModel();
        }
        $this->almaApiKeyModel = $almaApiKeyModel;
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\ShareOfCheckoutException
     */
    public function handleConsent()
    {
        if (!$this->almaApiKeyModel->isSameLiveApiKeySaved()) {
            try {
                $this->shareOfCheckoutHelper->resetShareOfCheckoutConsent();
            } catch (ShareOfCheckoutException $e) {
                throw new ShareOfCheckoutException($e->getMessage());
            }
        }
        if (
            $this->almaApiKeyModel->isSameLiveApiKeySaved() &&
            $this->shareOfCheckoutHelper->isShareOfCheckoutAnswered() &&
            $this->almaApiKeyModel->isSameModeSaved()
        ) {
            $this->shareOfCheckoutHelper->handleCheckoutConsent(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE . '_ON');
        }
    }
}
