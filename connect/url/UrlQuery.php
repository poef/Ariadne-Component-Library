<?php

	namespace ar\connect\url;
	
	class UrlQuery extends \ArrayObject implements KeyValueStoreInterface {
		
		public function __construct( $query ) {
			$arguments = array();
			if ( $query ) {
				// FIXME: parse_str cannot handle all types of query string
				// ?val&1+2=3  =>  val=&1_2=3
				parse_str( $query, $arguments );
				if ( class_exists('\ar\connect\http') && \ar\connect\http::$tainting ) {
					$arguments = \ar::taint($arguments);
				}
			}
			parent::__construct( $arguments, \ArrayObject::ARRAY_AS_PROPS );
		}
		
		public function getvar( $name ) {
			return $this->offsetGet($name);
		}
		
		public function putvar( $name, $value ) {
			$this->offsetSet($name, $value);
		}

		public function __toString() {
			$arguments = (array) $this;
			$arguments = \ar::untaint( $arguments, FILTER_UNSAFE_RAW );
			// FIXME: http_build_query cannot build all query strings, see above about parse_str
			$result = http_build_query( (array) $arguments );
			$result = str_replace( '%7E', '~', $result ); // incorrectly encoded, obviates need for oauth_encode_url
			return $result;
		}
		
		public function import( $values ) {
			if ( is_string( $values ) ) {
				parse_str( $values, $result );
				$values = $result;
			}
			if ( is_array( $values ) ) {
				foreach( $values as $name => $value ) {
					$this->offsetSet( $name, $value );
				}
			}
		}		

	}
