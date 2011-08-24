<?php
	namespace ar;
	
	class error extends \ar\Pluggable {
	
		static $throwExceptions = false;

		public static function isError($ob) {
			return ( is_object($ob) 
				&& ( $ob instanceof Exception || $ob instanceof PEAR_Error) );
		}

		public static function raiseError($message, $code, $previous = null) {
			if (self::$throwExceptions) {
				throw new error\ErrorException($message, $code, $previous);
			} else {
				return new error\ErrorException($message, $code, $previous);
			}
		}
		
		public static function configure( $option, $value ) {
			switch ($option) {
				case 'throwExceptions' : 
					self::$throwExceptions = $value;
				break;
			}
		}
	}
	

?>