<?php

namespace Integration\Application\Service;

use Db;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleInstallerService;
use PrestaShop\Module\Alma\Application\Service\ModuleService;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

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
        $this->moduleInstallerService = new ModuleInstallerService(
            $this->moduleService,
            $this->dbInstance
        );
    }

    /**
     * @return void
     */
    public function testInstallSuccessInstallModule()
    {
        // Drop alma table if exists to ensure installation
        $this->dbInstance->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'alma`');

        $this->assertTrue($this->moduleInstallerService->install());
    }
}
