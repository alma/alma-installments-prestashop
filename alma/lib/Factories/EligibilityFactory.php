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

namespace Alma\PrestaShop\Factories;

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\FeePlan;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class EligibilityFactory.
 */
class EligibilityFactory
{
    /**
     * @param array $data
     * @param FeePlan $feePlan
     *
     * @return Eligibility
     */
    public function createEligibility($data, $feePlan, $eligible = false)
    {
        return new Eligibility(
            [
                'installments_count' => $data['installmentsCount'],
                'deferred_days' => $data['deferredDays'],
                'deferred_months' => $data['deferredMonths'],
                'eligible' => $eligible,
                'constraints' => [
                    'purchase_amount' => [
                        'minimum' => $feePlan->min,
                        'maximum' => $feePlan->max,
                    ],
                ],
            ]
        );
    }
}
