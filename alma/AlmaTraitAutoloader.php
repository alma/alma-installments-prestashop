<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

/**
 * Alma Trait Autoloader
 */
class AlmaTraitAutoloader
{
    /**
     * Singleton (autoloader is loaded if instance is populated)
     *
     * @var AlmaTraitAutoloader
     */
    private static $instance;

    /**
     * The Constructor.
     *
     * @throws Exception if sp_autoload_register fail
     */
    public function __construct()
    {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }

        spl_autoload_register([$this, 'load_class']);
    }

    /**
     * Initialise auto loading
     */
    public static function autoload()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
    }

    /**
     * Include a class file.
     *
     * @param string $path as php file path
     *
     * @return bool successful or not
     */
    private function load_file($path)
    {
        if ($path && is_readable($path)) {
            include_once $path;
        }
    }

    /**
     * Auto-load Trait on demand.
     *
     * @param string $class as class name
     */
    public function load_class($class)
    {
        $pos = strrpos($class, '\\');
        if (false !== $pos) {
            // namespaced class name
            $classPath = str_replace('\\', \DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . \DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = $class;
            $className = '';
        }

        if (
            strpos($className, 'Trait') !== false
            && strpos($classPath, 'Alma/PrestaShop') === 0
        ) {
            $classPath = str_replace('\\', '/', substr($classPath, 16));
            $this->load_file(_PS_MODULE_DIR_ . 'alma/lib/' . $classPath . $className . '.php');
        }
    }
}
