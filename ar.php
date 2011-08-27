<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	if ( !defined('ARBaseDir') ) {
		define('ARBaseDir', dirname( __FILE__ ).'/' );
	}
	if ( !defined('ARDefineLoadMethod') ) {
		define('ARDefineLoadMethod', true);
	}
	require_once(ARBaseDir.'base.php');
	require_once(ARBaseDir.'exceptions.php');

	class ar extends ar\Pluggable {
		protected static $instances;
		protected static $ar;
		
		private static function _parseClassName($className) {
			$fileName = '';
			if (strpos($className, 'ar\\')===0) {
				$fileName = substr($className, 3);
				$fileName = preg_replace('/[^a-z0-9_\.\\\\\/]/i', '', $fileName);
				$fileName = str_replace('\\', '/', $fileName);
				$fileName = str_replace('_', '/', $fileName);
				$fileName = preg_replace('/\.\.+/', '.', $fileName);
			}
			return $fileName;
		}
		
		private static function _compileClassName($className) {
			if (strpos($className, 'ar\\')!==0) {
				$className = 'ar\\'.$className;
			}
			$className = preg_replace('/[^a-z0-9_\\\\]/i', '', $className);
			return $className;
		}

		public function __get($name) {
			return $this->load($name);
		}

		public static function load( $name = null ) {
			if (!$name) {
				if (!self::$ar) {
					self::$ar = new ar();
				}
				return self::$ar;
			} else {
				$fullName = self::_compileClassName($name);
				if (!self::$instances[$name]) {
					self::$instances[$name] = new $fullName();
				}
				return self::$instances[$name];
			}
		}
		
		public static function autoload($className) {
			if (strpos($className, 'ar\\')===0) {
				$fileName = self::_parseClassName( $className );
				if (file_exists(ARBaseDir.$fileName.'.php')) {
					require_once(ARBaseDir.$fileName.'.php');
				}
			}
		}
		
		public static function __invoke( $name = null ) {
			return $this->load( $name );
		}
	}
	
	if ( ARDefineLoadMethod && !function_exists('ar') ) {
	
		function ar( $name = null ) {
			return ar::load( $name );
		}

	}
	
	spl_autoload_register('ar::autoload');
?>