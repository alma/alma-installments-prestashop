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
require '../../config/config.inc.php';

require 'alma.php';

/*
 * Custom Handler for Error during Unit Testing :
 * While creating the HTML report, there was an issue :
 * "Generating code coverage report in HTML format ...count(): Parameter must be an array or an object that implements Countable"
 * This error seems to be due to incompatibilities with PHPUnit 5.7, but we can not upgrade it to stay compatible with PHP 5.6
 * The code below is a workaround to avoid this error.
 * It should be removed later when we have an upgrading plan for PHP 5.6
 * */
// Only apply this workaround for the HTML report
function isHtmlReportFromConfig()
{
    $configFilePath = __DIR__ . '/../phpunit.ci.xml';
    $configContent = file_get_contents($configFilePath);
    // Check & return if the coverage-html option is set
    return strpos($configContent, 'coverage-html') !== false;
}

function setErrorReportingLevel($level)
{
    error_reporting($level);
}

function restoreErrorReportingLevel($originalLevel)
{
    register_shutdown_function(function () use ($originalLevel) {
        setErrorReportingLevel($originalLevel);
    });
}

if (isHtmlReportFromConfig()) {
    $originalErrorReportingLevel = error_reporting();
    setErrorReportingLevel($originalErrorReportingLevel & ~E_WARNING);
    restoreErrorReportingLevel($originalErrorReportingLevel);
}
