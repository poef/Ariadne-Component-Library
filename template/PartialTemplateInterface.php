<?php

	namespace ar\template;

	interface PartialTemplateInterface {
	
		public function compile( $arguments = array() );
		
		public function run( );
		
		public function clean();
		
	}
?>