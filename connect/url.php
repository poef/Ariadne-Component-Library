<?php

	namespace ar\connect;

	class url {
		public static function url( $url ) {
			return new url\UrlElement( $url );
		}
	}
	
?>