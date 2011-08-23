<?php
	namespace ar\html;
	
	class menu extends \ar\Pluggable {

		public static function el() {
			$args       = func_get_args();
			$name       = array_shift($args);
			$attributes = array();
			$content    = array();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !( $arg instanceof \ar\xml\Nodes ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ($arg instanceof \ar\xml\Nodes) {
					$content    = array_merge( $content, (array) $arg);
				} else {
					$content[]  = $arg;
				}
			}
			if ( !count( $content ) ) {
				$content = null;
			} else {
				$content = new Nodes( $content );
			}
			
			return new menu\MenuElement( $name, $attributes, $content );
		}
		
		public static function element() {
			$args = func_get_args();
			return call_user_func_array( 'self::el', $args );
		}
		
	}

?>