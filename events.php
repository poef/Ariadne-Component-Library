<?php
	namespace ar;

	class events extends \ar\Pluggable {
		protected static $stack;
		
		public static function listen( $eventName, $objectType = null, $capture = false, $path = null ) {
			if (!self::$stack) {
				self::$stack = new events\Stack();
			}
			return self::$stack->listen( $eventName, $objectType, $capture, $path );
		}
		
		public static function capture( $eventName, $objectType = null, $capture = false, $path = null ) {
			if (!self::$stack) {
				self::$stack = new events\Stack();
			}
			return self::$stack->capture( $eventName, $objectType, $capture, $path );
		}
		
		public static function fire( $eventName, $eventData = array(), $objectType = null, $path = null ) {
			if (!self::$stack) {
				self::$stack = new events\Stack();
			}
			return self::$stack->fire( $eventName, $eventData, $objectType, $path );
		}
		
		public static function event() {
			if (!self::$stack) {
				self::$stack = new events\Stack();
			}
			return self::$stack->event();
		}
		
		public static function get( $path ) {
			if (!self::$stack) {
				self::$stack = new events\Stack();
			}			
			return new events\IncompleteListener( $path, null, null, false, self::$stack );
		}

	}
	
?>