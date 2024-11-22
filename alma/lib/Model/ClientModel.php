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

namespace Alma\PrestaShop\Model;

use Alma;
use Alma\API\Client;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ClientModel
{
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string|null
     */
    private $mode;

    public function __construct($apiKey, $mode = null)
    {
        $this->apiKey = $apiKey;
        $this->mode = $mode;
    }

    public function getClient()
    {
        $mode = $this->mode ?: SettingsHelper::getActiveMode();

        try {
            $alma = new Client($this->apiKey, [
                'mode' => $mode,
                'logger' => Logger::instance(),
            ]);

            $alma->addUserAgentComponent('PrestaShop', _PS_VERSION_);
            $alma->addUserAgentComponent('Alma for PrestaShop', Alma::VERSION);

            return $alma;
        } catch (Alma\API\DependenciesError $e) {
            Logger::instance()->error('[Alma] Dependencies Error creating Alma client', ['exception' => $e]);

            return null;
        } catch (Alma\API\ParamsError $e) {
            Logger::instance()->error('[Alma] Error creating Alma client', ['exception' => $e]);

            return null;
        }
    }

    public function getMerchantMe()
    {
        return $this->getClient()->merchants->me();
    }
}
