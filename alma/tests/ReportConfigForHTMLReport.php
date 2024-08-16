<?php
/*
 * Custom Handler for Error during Unit Testing :
 * While creating the HTML report, there was an issue :
 * "Generating code coverage report in HTML format ...count(): Parameter must be an array or an object that implements Countable"
 * This error seems to be due to incompatibilities with PHPUnit 5.7, but we can not upgrade it to stay compatible with PHP 5.6
 * The code below is a workaround to avoid this error.
 * It should be removed later when we have an upgrading plan for PHP 5.6
 * */

namespace Alma\PrestaShop\Tests;

class ReportConfigForHTMLReport
{
    private $configFilePath;

    public function __construct($configFilePath)
    {
        $this->configFilePath = $configFilePath;
    }

    public function isHtmlReportFromConfig()
    {
        $configContent = file_get_contents($this->configFilePath);
        // Check & return if the coverage-html option is set
        return strpos($configContent, 'coverage-html') !== false;
    }

    public function setErrorReportingLevel($level)
    {
        error_reporting($level);
    }

    public function restoreErrorReportingLevel($originalLevel)
    {
        register_shutdown_function(function () use ($originalLevel) {
            $this->setErrorReportingLevel($originalLevel);
        });
    }

    public function handleReportConfig()
    {
        if ($this->isHtmlReportFromConfig()) {
            $originalErrorReportingLevel = error_reporting();
            $this->setErrorReportingLevel($originalErrorReportingLevel & ~E_WARNING);
            $this->restoreErrorReportingLevel($originalErrorReportingLevel);
        }
    }
}
