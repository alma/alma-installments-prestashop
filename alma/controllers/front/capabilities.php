<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */


use Alma\API\Entities\Webhook;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaApiFrontController.php';

class AlmaCapabilitiesModuleFrontController extends AlmaApiFrontController
{
    public static function registerEndpoint($apiKey)
    {
        $alma = AlmaClient::createInstance($apiKey, Alma\API\LIVE_MODE);

        if (!$alma) {
            AlmaLogger::instance()->warning("Cannot register capabilities endpoint: no Alma client");
            return;
        }

        $endpointURL = Context::getContext()->link->getModuleLink('alma', 'capabilities');

        $webhookId = AlmaSettings::get('ALMA_CAPABILITIES_WEBHOOK_ID');
        if ($webhookId != null) {
            try {
                $webhook = $alma->webhooks->fetch($webhookId);
            } catch (\Alma\API\RequestError $e) {
                if ($e->response->responseCode == 404) {
                    $webhookId = null;
                    AlmaSettings::updateValue('ALMA_CAPABILITIES_WEBHOOK_ID', $webhookId);
                } else {
                    AlmaLogger::instance()->warning("Could not fetch registered capabilities webhook");
                }
            }

            if ((isset($webhook) && $webhook->url != $endpointURL) || (!isset($webhook) && $webhookId)) {
                try {
                    $alma->webhooks->delete($webhookId);
                } catch (\Alma\API\RequestError $e) {
                    AlmaLogger::instance()->warning("Could not delete outdated capabilities webhook");
                    return;
                }
            }
        }

        try {
            $webhook = $alma->webhooks->create(Webhook::TYPE_INTEGRATION_CAPABILITIES, $endpointURL);
            AlmaSettings::updateValue('ALMA_CAPABILITIES_WEBHOOK_ID', $webhook->id);
        } catch (Exception $e) {
            AlmaLogger::instance()->warning("Cannot register capabilities endpoint: " . $e->getMessage());
        }
    }

    public function postProcess()
    {
        parent::postProcess();

        header('Content-Type: application/json');

        $data = array(
            'platform' => array(
                'name' => 'Prestashop',
                'version' => _PS_VERSION_,
                'module_version' => Alma::VERSION,
                'php_version' => rtrim(str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION), '-'),
                'max_execution_time' => (int)ini_get('max_execution_time'),
            ),
            'webhooks' => array(
                array(
                    'webhook' => 'share_of_checkout',
                    'endpoint' => Context::getContext()->link->getModuleLink('alma', 'share_of_checkout'),
                )
            )
        );

        $this->ajaxDie(json_encode(array('success' => true, 'data' => $data)));
    }

}
