<?php
/**
 * Alma Autoloader
 *
 * @package Alma_Autoloader
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Alma Autoloader.
 */
class Alma_Autoloader {
	/**
	 * Singleton (autoloader is loaded if instance is populated)
	 *
	 * @var Alma_Autoloader
	 */
	private static $instance;

	/**
	 * The Constructor.
	 *
	 * @throws Exception If sp_autoload_register fail.
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'load_class' ) );
	}

	/**
	 * Initialise auto loading
	 */
	public static function autoload() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
	}


	/**
	 * Include a class file.
	 *
	 * @param string $path as php file path.
	 *
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
		} else {
			var_dump("WRONG FILE", $path);
			die;
		}
	}

	/**
	 * Auto-load WC classes on demand to reduce memory consumption.
	 *
	 * @param string $class as class name.
	 */
	public function load_class( $class ) {
		if (strpos( $class, 'Trait' ) !== -1) {
			$this->load_file(_PS_MODULE_DIR_ . '/alma/lib/Utils'.$class.'.php');
		}
	}
}
