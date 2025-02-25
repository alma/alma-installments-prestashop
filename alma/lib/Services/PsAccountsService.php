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
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Helpers\MediaHelper;
use Exception;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PsAccountsService
{
    /**
     * @var \Alma
     */
    private $module;
    /**
     * @var MediaHelper
     */
    private $mediaHelper;
    /**
     * @var ServiceContainer
     */
    private $container;
    /**
     * @var \Context
     */
    private $context;

    public function __construct(
        $module,
        $context,
        $mediaHelper = null
    ) {
        $this->module = $module;
        $this->context = $context;
        if (!$mediaHelper) {
            $mediaHelper = (new MediaHelperBuilder())->getInstance();
        }
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * Check and Install PS Account during the installation module
     *
     * @return void
     */
    public function install()
    {
        try {
            $this->setContainer();
            $this->getService('alma.ps_accounts_installer')->install();
        } catch (CompatibilityPsAccountsException $e) {
            LoggerFactory::instance()->info($e->getMessage());
        }
    }

    /**
     * @return bool
     *
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException
     * @throws \Exception
     */
    public function renderPSAccounts()
    {
        $this->setContainer();

        try {
            $accountsFacade = $this->getService('alma.ps_accounts_facade');
            /** @var \PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts $accountsFacade */
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (InstallerException $e) {
            /** @var \PrestaShop\PsAccountsInstaller\Installer\Installer $accountsInstaller */
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
        $this->container = new ServiceContainer(
            $this->module->name,
            $this->module->getLocalPath()
        );
    }
}
