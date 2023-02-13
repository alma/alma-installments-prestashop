<?php
/**
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Hooks;

use Alma;
use Configuration;
use Context;
use Cookie;
use Employee;
use Tools;
use Validate;
use WebserviceKey;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class HookController
{
    /** @var Alma */
    protected $module;

    /** @var Context */
    protected $context;

    /**
     * HookController constructor.
     *
     * @param $module Alma
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    protected function loggedAsEmployee()
    {
        $cookie = new Cookie('psAdmin', '', (int) Configuration::get('PS_COOKIE_LIFETIME_BO'));
        $cookie->disallowWriting();
        $employee = new Employee((int) $cookie->id_employee);

        return Validate::isLoadedObject($employee)
            && $employee->checkPassword((int) $cookie->id_employee, $cookie->passwd)
            && (!isset($cookie->remote_addr)
                || $cookie->remote_addr == ip2long(Tools::getRemoteAddr())
                || !Configuration::get('PS_COOKIE_CHECKIP'));
    }

    /**
     * Checks if we are in a Webservice (API user) call.
     *
     * This method does not check API user permissions or if he is enabled.
     *
     * @return bool
     */
    protected function isKnownApiUser()
    {
        $tokenApi = $_SERVER['PHP_AUTH_USER'];

        return boolval(WebserviceKey::keyExists($tokenApi));
    }

    abstract public function run($params);

    public function canRun()
    {
        return true;
    }

    protected function currentControllerName()
    {
        $controller = $this->context->controller;

        return !empty($controller->php_self)
            ? preg_replace('/[[:^alnum:]]+/', '', $controller->php_self)
            : explode('Controller', get_class($controller))[0];
    }
}
