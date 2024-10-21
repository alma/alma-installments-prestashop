<?php

namespace Alma\PrestaShop\Tests\Unit\Controllers\Front;

use Alma\PrestaShop\Helpers\ConfigurationHelper;
use PHPUnit\Framework\TestCase;

include __DIR__ . '/../../../../controllers/front/configexport.php';
class AlmaConfigurationExportTestModuleFrontController extends TestCase
{
    protected $configHelper;
    protected $moduleFrontController;
    protected function setUp()
    {
        $this->configHelper = $this->createMock(ConfigurationHelper::class);
        $this->moduleFrontController =  \Mockery::mock(\AlmaConfigExportModuleFrontController::class)->makePartial();
        $this->moduleFrontController->setConfigHelper($this->configHelper);

    }
    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testModule()
    {
        $this->assertInstanceOf(\AlmaConfigExportModuleFrontController::class, $this->moduleFrontController);
    }

    public function testConfigHelperSet()
    {
        $this->configHelper->expects($this->once())
            ->method('get')
            ->with('TEST_DATA')
            ->willReturn('test');

        $this->assertEquals('test', $this->moduleFrontController->getTestData());
    }
}