<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 *
	 * This file must be included for the Ariadne Component Library to work
	 * If you want to keep this library fully PSR-0 compliant, move this file
	 * one directory up.
	 */

	if ( !defined('AR_BASE_DIR') ) {
		define('AR_BASE_DIR', dirname( __FILE__ ).'/' );
	}
	require_once( AR_BASE_DIR . 'PluggableInterface.php' );
	require_once( AR_BASE_DIR . 'Pluggable.php' );
	
	class ar extends \ar\Pluggable {
	
		protected static $instances;
		protected static $ar;
		
		private static function _parseClassName( $className ) {
			$fileName = '';
			if ( strpos( $className, 'ar\\' ) === 0 ) {
				$fileName = substr( $className, 3 );
				$fileName = preg_replace( '/[^a-z0-9_\.\\\\\/]/i', '', $fileName );
				$fileName = str_replace( '\\', '/', $fileName );
				$fileName = str_replace( '_', '/', $fileName );
				$fileName = preg_replace( '/\.\.+/', '.', $fileName );
			}
			return $fileName . '.php';
		}
		
		public function __invoke( $name ) {
			return $this->autoload( $name );
		}
		
		public static function autoload($className) {
			if ( strpos( $className, 'ar\\' ) === 0 ) {
				$fileName = self::_parseClassName( $className );
				require_once( AR_BASE_DIR . $fileName );
			}
		}
		
	}

	spl_autoload_register('ar::autoload');
?>