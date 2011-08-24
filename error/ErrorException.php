<?php

	namespace ar\error;

	class ErrorException extends \ar\Exception {
		
		public function __toString() {
			return $this->getCode() . ": " . $this->getMessage() . "\r\n";
		}
		
	}
	
?>