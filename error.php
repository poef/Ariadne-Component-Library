<?php
	namespace ar;
	
	\ar::registerMethod( 'error', '\ar\errors::raiseError' );
	\ar::registerMethod( 'isError', '\ar\errors::isError' );

	class errors {
	
		static $throwExceptions = false;

		public static function isError($ob) {
			return ( is_object($ob) 
				&& ( $ob instanceof ErrorInstance || $ob instanceof PEAR_Error) );
		}

		public static function raiseError($message, $code, $previous = null) {
			if (self::$throwExceptions) {
				throw new ErrorInstance($message, $code, $previous);
			} else {
				return new ErrorInstance($message, $code, $previous);
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
	
	class Error extends exceptionDefault {
		
		public function __toString() {
			return $this->getCode() . ": " . $this->getMessage() . "\r\n";
		}
		
	}
	
?>