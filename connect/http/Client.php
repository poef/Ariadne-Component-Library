<?php
	namespace ar\connect\http;

	interface Client {

		public function get( $url, $request = null, $options = array() );

		public function post( $url, $request = null, $options = array() );

		public function put( $url, $request = null, $options = array() );

		public function delete( $url, $request = null, $options = array() );

		public function send( $type, $url, $request = null, $options = array() );
		
		public function headers( $headers );

	}
	
?>