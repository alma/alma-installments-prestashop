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

use Alma\PrestaShop\Builders\Models\MediaHelperBuilder;
use Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException;
use Alma\PrestaShop\Helpers\ToolsHelper;
use Alma\PrestaShop\Logger;
use Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PsAccountService
{
    const PS_ACCOUNTS_VERSION_REQUIRED = '5.3.0';
    /**
     * @var \Module
     */
    private $module;
    /**
     * @var \Alma\PrestaShop\Helpers\MediaHelper
     */
    private $mediaHelper;
    /**
     * @var \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer
     */
    private $container;
    /**
     * @var \Alma\PrestaShop\Helpers\ToolsHelper
     */
    private $toolsHelper;
    /**
     * @var \Context
     */
    private $context;

    public function __construct($module, $context)
    {
        $this->module = $module;
        $this->context = $context;
        $this->mediaHelper = (new MediaHelperBuilder())->getInstance();
        $this->toolsHelper = new ToolsHelper();
    }

    /**
     * Check and Install PS Account during the install module
     *
     * @return void
     */
    public function install()
    {
        try {
            $this->checkPsAccountsPresence();
            $this->setContainer();
            $this->getService('alma.ps_accounts_installer')->install();
        } catch (CompatibilityPsAccountsException $e) {
            Logger::instance()->info($e->getMessage());
        }
    }

    /**
     * @return bool
     *
     * @throws \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     */
    public function renderPSAccounts()
    {
        $this->checkPsAccountsCompatibility();
        $this->setContainer();

        try {
            $accountsFacade = $this->getService('alma.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            $accountsInstaller = $this->getService('alma.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsFacade = $this->getService('alma.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        try {
            $this->mediaHelper->addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->module->name),
            ]);

            // Retrieve the PrestaShop Account CDN
            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());

            return true;
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();

            return false;
        }
    }

    /**
     * Retrieve the service
     *
     * @param string $serviceName
     *
     * @return object|null
     */
    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    /**
     * Set container for Ps Account
     *
     * @return void
     */
    public function setContainer()
    {
        $this->container = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
            $this->module->name,
            $this->module->getLocalPath()
        );
    }

    /**
     * Check if PS Account is installed and up to date, minimal version required 5.0.
     *
     * @return void
     *
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException
     */
    public function checkPsAccountsCompatibility()
    {
        $this->checkPsAccountsPresence();
        $psAccountsModule = \Module::getInstanceByName('ps_accounts');
        if (!$psAccountsModule) {
            throw new \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException('[Alma] PS Account is not installed');
        }

        if ($psAccountsModule->version < self::PS_ACCOUNTS_VERSION_REQUIRED) {
            throw new \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException('[Alma] PS Account is not up to date, minimal version required ' . self::PS_ACCOUNTS_VERSION_REQUIRED);
        }
    }

    /**
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException
     */
    public function checkPsAccountsPresence()
    {
        if (_PS_MODE_DEV_ === true) {
            throw new \Alma\PrestaShop\Exceptions\CompatibilityPsAccountsException('[Alma] Debug mode is activated');
        }

        if (
            !class_exists(\Symfony\Component\Config\ConfigCache::class)
            || !class_exists(\PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer::class)
        ) {
            throw new CompatibilityPsAccountsException('[Alma] Classes don\'t exist for PS Account');
        }
        if (
            $this->toolsHelper->psVersionCompare('1.6', '<')
        ) {
            throw new CompatibilityPsAccountsException('[Alma] Prestashop version lower than 1.6');
        }
    }
}
