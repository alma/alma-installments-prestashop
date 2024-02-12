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

namespace Alma\PrestaShop\Helpers;

use Alma\API\Exceptions\ParametersException;
use Alma\API\Exceptions\RequestException;
use Alma\API\RequestError;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SubscriptionHelper
 */
class SubscriptionHelper
{
    use AjaxTrait;

    /**
     * @var ClientHelper
     */
    protected $almaApiClient;

    public function __construct()
    {
        $this->almaApiClient = ClientHelper::defaultInstance();
    }

    /**
     * Used for Unit Test
     * @param $client
     * @return void
     */
    public function setPhpClient($client)
    {
        $this->almaApiClient = $client;
    }

    /**
     * @param $action
     * @param $sid
     * @param $trace
     * @throws RequestError
     * @throws SubscriptionException
     * @throws \PrestaShopException
     */
    public function postProcess($action, $sid, $trace)
    {
        if (!$sid) {
            Logger::instance()->error('Sid is missing');
            throw new SubscriptionException('Sid is missing');

            $this->ajaxRenderAndExit(json_encode(['error' => 'Missing Id']), 500);
        }

        if (!$trace) {
            $this->ajaxRenderAndExit(json_encode(['error' => 'Missing secutiry token']), 500);
        }

        switch ($action) {
            case  'update' :
                try {
                    $subscription = $this->almaApiClient->insurance->getSubscription(['id' => $sid]);
                } catch (ParametersException $e) {

                } catch (RequestException $e) {

                }
                // @TODO : Get du subscription PHP Client
                // @TODO : if error get subscription log error + throw error
                // @TODO : if error check data in get subscription Log error + throw error
                // @TODO : Update in database susbcription_state with the new state
                // @TOTO : set notification order message with link to the order in the message
                $this->ajaxRenderAndExit(json_encode(['success' => true]), 200);
            default :
                // @TODO : Log error + throw error
                $this->ajaxRenderAndExit(json_encode(['error' => 'Wrong action']), 500);
        }
    }
}
