<?php
	namespace ar;
		
	class tainting {
	
		public static function taint($value) {
			if ( is_numeric($value) ) {
				return;
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, array( self, 'taint' ) );
			} else if ( is_string($value) && $value ) { // empty strings don't need tainting
				$value = new \ar\Tainted($value);
			}
			return $value;
		}

		public static function untaint($value, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null) {
			if ( $value instanceof \ar\Tainted ) {
				$value = filter_var($value->value, $filter, $flags);
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, array( self, 'untaintArrayItem'), array( 
					'filter' => $filter,
					'flags' => $flags
				) );
			}
			return $value;
		}

	}

	class Tainted {
		public $value = null;

		public function __construct($value) {
			$this->value = $value;
		}

		public function __toString() {
			return filter_var($this->value, FILTER_SANITIZE_SPECIAL_CHARS);
		}
	}	
	
?>