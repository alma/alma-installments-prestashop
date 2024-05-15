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

namespace Alma\PrestaShop\Tests\Unit\Factories;

use Alma\API\Endpoints\Results\Eligibility;
use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Factories\EligibilityFactory;
use PHPUnit\Framework\TestCase;

class EligibilityFactoryTest extends TestCase
{
    /**
     * @var EligibilityFactory
     */
    protected $eligibilityFactory;

    public function setUp()
    {
        $this->eligibilityFactory = new EligibilityFactory();
    }

    public function testCreateEligibility()
    {
        $data = [
            'installmentsCount' => 1,
            'deferredDays' => 0,
            'deferredMonths' => 0,
        ];

        $feePlan = new FeePlan(['min' => 10, 'max' => 1000]);

        $eligibility = new Eligibility(
            [
                'installments_count' => 1,
                'deferred_days' => 0,
                'deferred_months' => 0,
                'eligible' => false,
                'constraints' => [
                    'purchase_amount' => [
                        'minimum' => 10,
                        'maximum' => 1000,
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(Eligibility::class, $this->eligibilityFactory->createEligibility($data, $feePlan));

        $this->assertEquals($eligibility, $this->eligibilityFactory->createEligibility($data, $feePlan));

        $eligibility = new Eligibility(
            [
                'installments_count' => 1,
                'deferred_days' => 0,
                'deferred_months' => 0,
                'eligible' => true,
                'constraints' => [
                    'purchase_amount' => [
                        'minimum' => 10,
                        'maximum' => 1000,
                    ],
                ],
            ]
        );

        $this->assertEquals($eligibility, $this->eligibilityFactory->createEligibility($data, $feePlan, true));
    }
}
