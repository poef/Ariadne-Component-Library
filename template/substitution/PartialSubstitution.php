<?php

	namespace ar\template\substitution;

	class PartialSubstitution implements \ar\template\PartialTemplateInterface {
		
		private $defaults = array();
		private $text = '';
		
		public function __construct( $text, $defaultArguments ) {
			$this->defaults = $defaultArguments;
			$this->text = $text;
		}
	
		public function compile( $arguments = array() ) {
			$engine = new \ar\template\SubstitutionEngine( $this->defaults );
			return $engine->compile( $this->text, $arguments );
		}
		
		public function __toString( ) {
			return '' . $this->clean();
		}
		
		public function run() {
			echo '' . $this;
		}
		
		public function clean() {
			return preg_replace( '/([^\\]|\b)\{(\$)?([^}]+)\}/m', '$1', (string) $this->text );
		}
		
	}
	
?>