<?php

namespace Integration\Application\Service;

use Db;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleInstallerService;
use PrestaShop\Module\Alma\Application\Service\ModuleService;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class ModuleInstallerServiceTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Service\ModuleInstallerService
     */
    private ModuleInstallerService $moduleInstallerService;
    /**
     * @var \Db
     */
    private $dbInstance;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->languageRepository = new LanguageRepository();
        $this->moduleService = new ModuleService(
            \Module::getInstanceByName('alma'),
            $this->languageRepository
        );
        $this->dbInstance = Db::getInstance();
        $this->psAccountInstaller = new Installer('5.3');
        $this->moduleInstallerService = new ModuleInstallerService(
            $this->moduleService,
            $this->dbInstance,
            $this->psAccountInstaller
        );
    }

    /**
     * @return void
     */
    public function testInstallSuccessInstallModule()
    {
        $this->assertTrue($this->moduleInstallerService->install());
    }

    /**
     * @return void
     * @throws \PrestaShopException
     */
    public function tearDown(): void
    {
        // Uninstall tabs after test
        $this->moduleService->uninstallTabs(ModuleInstallerService::TABS);

        // Drop alma table if exists after test to clean up
        $this->dbInstance->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'alma`');
        parent::tearDown();
    }
}
