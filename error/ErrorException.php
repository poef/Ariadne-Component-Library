<?php

	namespace ar\error;

	class ErrorException extends exceptionDefault {
		
		public function __toString() {
			return $this->getCode() . ": " . $this->getMessage() . "\r\n";
		}
		
	}
	
?>