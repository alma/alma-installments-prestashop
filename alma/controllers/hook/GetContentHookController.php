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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Exceptions\AlmaApiKeyException;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Exceptions\MissingParameterException;
use Alma\PrestaShop\Exceptions\PnxFormException;
use Alma\PrestaShop\Exceptions\ShareOfCheckoutException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
use Alma\PrestaShop\Services\ConfigFormService;
use Alma\PrestaShop\Services\InsuranceService;

final class GetContentHookController extends AdminHookController
{
    /** @var \Module */
    protected $module;

    /**
     * @var \Alma\PrestaShop\Services\ConfigFormService
     */
    protected $configFormService;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    protected $toolsProxy;
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    protected $configurationProxy;
    /**
     * @var \Alma\PrestaShop\Services\InsuranceService
     */
    protected $insuranceService;

    /**
     * GetContentHook Controller construct.
     *
     * @codeCoverageIgnore
     */
    public function __construct($module)
    {
        $contextFactory = new ContextFactory();

        $this->configFormService = new ConfigFormService(
            $module,
            $contextFactory->getContext()
        );

        $this->toolsProxy = new ToolsProxy();
        $this->configurationProxy = new ConfigurationProxy();
        $this->insuranceService = new InsuranceService();

        parent::__construct($module);
    }

    /**
     * @return array
     */
    protected function assignSmartyKeys()
    {
        $token = $this->toolsProxy->getAdminTokenLite('AdminModules');
        $href = $this->context->link->getAdminLink('AdminParentModulesSf', $token);

        $assignSmartyKeys = [
            'hasPSAccounts' => false, // Dynamic content
            'updated' => false, // Dynamic content
            'suggestPSAccounts' => false, // Dynamic content
            'validation_error_classes' => 'alert alert-danger',
            'tip_classes' => 'alert alert-info',
            'success_classes' => 'alert alert-success',
            'breadcrumbs2' => [
                'container' => [
                    'name' => $this->module->l('Modules', 'GetContentHookController'),
                    'href' => $href,
                ],
                'tab' => [
                    'name' => $this->module->l('Module Manager', 'GetContentHookController'),
                    'href' => $href,
                ],
            ],
            'quick_access_current_link_name' => $this->module->l('Module Manager - List', 'GetContentHookController'),
            'quick_access_current_link_icon' => 'icon-AdminParentModulesSf',
            'token' => $token,
            'host_mode' => 0,
            'validation_error' => '', //Add error key
            'validation_message' => '', //Add error message
            'hasKey' => false, //Return true if api key is set
            'tip' => 'fill_api_keys',
        ];

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $assignSmartyKeys['validation_error_classes'] = 'alert';
            $assignSmartyKeys['tip_classes'] = 'conf';
            $assignSmartyKeys['success_classes'] = 'conf';
        }

        return $assignSmartyKeys;
    }

    /**
     * Execute the controller to display the configuration form and save the config if submit value alma_config_form
     *
     * @param $params
     *
     * @return string
     */
    public function run($params)
    {
        $assignSmartyKeys = $this->assignSmartyKeys();
        $messages = [];

        if ($this->toolsProxy->isSubmit('alma_config_form')) {
            try {
                $this->configFormService->saveConfigurations();
            } catch (AlmaApiKeyException $e) {
                $messages[] = $e->getMessage();
            } catch (ShareOfCheckoutException $e) {
                $messages[] = $e->getMessage();
            } catch (PnxFormException $e) {
                $messages[] = $e->getMessage();
            } catch (ClientException $e) {
                Logger::instance()->error(
                    sprintf(
                        '[Alma] Error sending URL for gather CMS data: %s',
                        $e->getMessage()
                    )
                );
            } catch (MissingParameterException $e) {
                Logger::instance()->error($e->getMessage());
                $messages[] = 'Please fill in all required settings';
            }
        }

        $assignSmartyKeys['form'] = $this->configFormService->getRenderPaymentFormHtml();
        $assignSmartyKeys['hasKey'] = $this->configurationProxy->get(ConfigFormService::ALMA_FULLY_CONFIGURED);
        $assignSmartyKeys['error_messages'] = $messages;
        $this->context->smarty->assign($assignSmartyKeys);

        return $this->module->display($this->module->file, 'configurationContent.tpl');
    }
}
