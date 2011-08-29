<?php

	\ar\template::registerMethod( 'substitute', function( $text = null, $arguments = null ) {
		if ( !isset( $text ) ) {
			return new \ar\template\substitution\SubstitutionEngine( $arguments );
		} else {
			$engine = new \ar\template\substitution\SubstitutionEngine();
			return $engine->compile( $text, $arguments );
		}
	} );
	
?>