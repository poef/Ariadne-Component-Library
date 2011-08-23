<?php
	namespace ar\connect\url;
	
	class UrlElement implements \ar\KeyValueStoreInterface {
	
		private $components, $query;
		
		public function __construct( $url ) {
			$this->components = parse_url( $url );
			// FIXME: make option to skip parsing of the query part
			$this->query = new UrlQuery( $this->components['query'] );
		}
		
		public function __get($var) {
			if ( $var == 'password' ) {
				$var = 'pass';
			}
			if ( $var == 'query' ) {
				return $this->query;
			} else if ( isset( $this->components[$var] ) ) {
				return $this->components[$var];
			} else {
				return null;
			}
		}
		
		public function __set($var, $value) {
			switch($var) {
				case 'query' :
					if ( is_string( $value ) ) {
						$this->query = new UrlQuery( $query );
					} else if ( $value instanceof UrlQuery ) {
						$this->query = $value;
					} else if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
						$this->query = new UrlQuery( $value );
					}
				break;
				case 'path' :
					$this->components[$var] = $value;
				break;
				case 'password' :
					$var = 'pass';
					$this->components[$var] = $value;
				break;
				case 'scheme':
				case 'host' :
				case 'port' :
				case 'user' :
				case 'pass' :
				case 'fragment' :
					$this->components[$var] = $value;
				break;
			}
		}

		public function __toString() {
			$url = '';
			if ( $this->components['host'] ) {
				if ( $this->components['scheme'] ) {
					$url .= $this->components['scheme'] . '://';
				}
				if ( $this->components['user'] ) {
					$url .= $this->components['user'];
					if ( $this->components['pass'] ) {
						$url .= ':' . $this->components['pass'];
					}
					$url .= '@';
				}
				$url .= $this->components['host'];
				if ( $this->components['port'] ) {
					$url .= ':' . $this->components['port'];
				}
				if ( $this->components['path'] ) {
					if ( substr( $this->components['path'], 0, 1 ) !== '/' ) {
						$url .= '/';
					}
				}
			}
			$url .= $this->components['path'];
			$query = '' . $this->query;
			if ($query) {
				$url .= '?' . $query ;
			}
			if ( $this->components['fragment'] ) {
				$url .= '#' . $this->components['fragment'];
			}
			return $url;
		}
		
		public function getvar( $name ) {
			return $this->query->$name;
		}
		
		public function putvar( $name, $value ) {
			$this->query->{$name} = $value;
		}

		public function import( $values ) {
			$this->query->import( $values );
		}
		
	}
?>