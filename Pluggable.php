<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */
	
	namespace ar;	
	
	class Pluggable implements PluggableInterface {
	
		protected static $plugins;
		
		protected static function _callPlugin( $methodName, $arguments = array() ) {
			if ( !self::$plugins[$methodName] ) {
				// try to trigger autoloading of plugins
				$dummyClass = get_called_class() . '\plugins\\' . $methodName;
				class_exists( $dummyClass );
			}
			if ( !self::$plugins[$methodName] ) {
				throw new ExceptionDefault( 'Method '.$methodName.' not available. Is the required plugin loaded?', exceptions::OBJECT_NOT_FOUND );
			} else {
				return call_user_func_array( self::$plugins[$methodName], $arguments );
			}
		}

		public static function registerMethod( $methodName, $method ) {
			self::$plugins[$methodName] = $method;
		}

		public static function __callStatic( $name, $arguments ) {
			return self::_callPlugin( $name, $arguments );
		}

		public function __call( $name, $arguments ) {
			return $this->_callPlugin( $name, $arguments );
		}
	}
?>