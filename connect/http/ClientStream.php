<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace ar\connect\http;
	
	class ClientStream implements \ar\connect\http\Client {

		private $options = array();

		public $responseHeaders = null;

		private function parseRequestURL( $url ) {
			$request = explode( '?', (string) $url );
			if ( isset($request[1]) ) {
				return $request[1];
			} else {
				return null;
			}
		}

		private function compileRequest( array $request ) {
			$result = "";
			foreach ( $request as $key => $value ) { 
				if ( !is_integer( $key ) ) {
					$result .= urlencode($key)."=".urlencode($val)."&"; 
				}
			} 
			return $result;	
		}

		private function mergeOptions( ) {
			$args = func_get_args();
			array_unshift( $args, $this->options );
			return call_user_func_array( 'array_merge', $args );
		}

		public function send( $type, $url, $request = null, $options = array() ) {
			if ( is_array( $request ) ) {
				$request = $this->compileRequest( $request );
			}
			$options = $this->mergeOptions( array(
				'method' => $type,
				'content' => $request
			), $options );
			$context = stream_context_create( array( 'http' => $options ) );
			$result = @file_get_contents( (string) $url, false, $context );
			$this->responseHeaders = $http_response_header; //magic php variable set by file_get_contents.
			$this->requestHeaders = $options['header'];
			return $result;
		}

		public function __construct( $options = array() ) {
			$this->options = $options;
		}

		public function get( $url, $request = null, $options = array() ) {
			
			if ( !isset($request) ) {
				$request = $this->parseRequestURL($url);
			}
			return $this->send( 'GET', $url, $request, $options );		
		}
		
		public function post( $url, $request = null, $options = array() ) {
			return $this->send( 'POST', $url, $request, $options );		
		}

		public function put( $url, $request = null, $options = array() ) {
			return $this->send( 'PUT', $url, $request, $options );		
		}

		public function delete( $url, $request = null, $options = array() ) {
			return $this->send( 'DELETE', $url, $request, $options );		
		}

		public function headers( $headers ) {
			if (is_array($headers)) {
				$headers = join("\r\n", $headers);
			}
			$this->options['header'] = $this->options['headers'].$headers;
			return $this;
		}
		
	}
?>