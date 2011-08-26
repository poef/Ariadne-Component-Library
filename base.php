<?php
	namespace ar;	

	interface KeyValueStoreInterface {
	
		public function getvar( $name );
		
		public function putvar( $name, $value );
	
	}
	
	interface PluggableInterface {

		public static function registerMethod( $methodName, $method );
	
	}
	
	class Pluggable implements PluggableInterface {
		protected static $plugins;
		
		protected static function _callPlugin($methodName, $arguments = array() ) {
			if (!self::$plugins[$methodName]) {
				// try to trigger autoloading of plugins
				$dummyClass = get_called_class() . '\plugins\\' . $methodName;
				class_exists( $dummyClass );
			}
			if (!self::$plugins[$methodName]) {
				throw new ExceptionDefault( 'Method '.$methodName.' not available. Is the required plugin loaded?', exceptions::OBJECT_NOT_FOUND );
			} else {
				return call_user_func_array( self::$plugins[$methodName], $arguments );
			}
		}

		public static function registerMethod( $methodName, $method ) {
			self::$plugins[$methodName] = $method;
		}

		public static function __callStatic($name, $arguments) {
			return self::_callPlugin($name, $arguments);
		}

		public function __call($name, $arguments) {
			return $this->_callPlugin($name, $arguments);
		}
	}
?>