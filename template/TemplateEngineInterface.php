<?php

	namespace ar\template;

	interface TemplateEngineInterface {
	
		public function compile( $template, $arguments = null );
		
		public function run( $template, $arguments = null );
		
	}
?>