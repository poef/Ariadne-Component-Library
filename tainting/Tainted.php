<?php
	namespace ar\tainting;
	
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