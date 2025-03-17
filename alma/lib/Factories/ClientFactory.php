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

use Alma;
use Alma\API\Client;
use Alma\API\DependenciesError;
use Alma\API\ParamsError;

if (!defined('_PS_VERSION_')) {
    exit;
}
class ClientFactory
{
    /**
     * @var Client
     */
    private $alma = null;

    /**
     * @return Client|null
     */
    public function create($apiKey, $mode)
    {
        try {
            $this->alma = new Client($apiKey, [
                'mode' => $mode,
                'logger' => LoggerFactory::instance(),
            ]);

            $this->alma->addUserAgentComponent('PrestaShop', _PS_VERSION_);
            $this->alma->addUserAgentComponent('Alma for PrestaShop', Alma::VERSION);

            return $this->alma;
        } catch (DependenciesError $e) {
            LoggerFactory::instance()->error('[Alma] Dependencies Error creating Alma client', ['exception' => $e]);

            return null;
        } catch (ParamsError $e) {
            LoggerFactory::instance()->error('[Alma] Error creating Alma client', ['exception' => $e]);

            return null;
        }
    }

    /**
     * @return Client|null
     */
    public function get($apiKey, $mode)
    {
        if (!$this->alma) {
            $this->alma = $this->create($apiKey, $mode);
        }

        return $this->alma;
    }
}
