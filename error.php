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