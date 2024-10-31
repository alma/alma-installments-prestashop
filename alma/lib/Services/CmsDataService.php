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

use Alma\API\Lib\PayloadFormatter;
use Alma\PrestaShop\Helpers\CmsDataHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CmsDataService
{
    const ALMA_CMSDATA_DATE = 'ALMA_CMSDATA_DATE';

    /**
     * @var PayloadFormatter
     */
    protected $payloadFormatter;
    /**
     * @var \Alma\PrestaShop\Helpers\CmsDataHelper
     */
    protected $cmsDataHelper;

    /**
     * CmsDataService constructor.
     */
    public function __construct()
    {
        $this->cmsDataHelper = new CmsDataHelper();
        $this->payloadFormatter = new PayloadFormatter();
    }

    /**
     * @return array
     */
    public function getPayloadCmsData()
    {
        $cmsInfo = $this->cmsDataHelper->getCmsInfoArray();
        $cmsFeature = $this->cmsDataHelper->getCmsFeatureArray();

        return $this->payloadFormatter->formatConfigurationPayload($cmsInfo, $cmsFeature);
    }
}
