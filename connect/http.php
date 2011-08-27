<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace ar\connect;
	
	class http extends \ar\Pluggable {
	
		private static $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE;  //needed to make __get() work
		public static $tainting = true;
		
		public function __get($var) {
			switch ($var) {
				case '_GET' : 
					return $this->getvar( null, 'GET');
				break;
				case '_POST' : 
					return $this->getvar( null, 'POST');
				break;
				case '_REQUEST' : 
					return $this->getvar();
				break;
				case '_SERVER' :
					return $this->getvar( null, 'SERVER');
				break;
				case '_COOKIE' :
					return $this->getvar( null, 'COOKIE');
				break;
			}
		}
		
		public static function getvar( $name = null, $method = null) {
			$result = null;
			switch($method) {
				case 'GET' : 
					$result = isset($name) ? $_GET[$name] : $_GET;
				break;
				case 'POST' : 
					$result = isset($name) ? $_POST[$name] : $_POST;
				break;
				case 'COOKIE' :
					$result = isset($name) ? $_COOKIE[$name] : $_COOKIE;
				break;
				case 'SERVER' :
					$result = isset($name) ? $_SERVER[$name] : $_SERVER;
				break;
				default : 
					$result = !isset($name) ? $_REQUEST : 
						( isset($_POST[$name]) ? $_POST[$name] : $_GET[$name] );
				break;
			}
			if (self::$tainting) {
				$result = ar::taint( $result );
			}
			return $result;
		}

		public static function request( $method = null, $url = null, $postdata = null, $options = array() ) {
			$client = new http\ClientStream();
			return $client->send( $method, $url, $postdata, $options );
		}

		public static function client( $options = array() ) {
			return new http\ClientStream( $options );
		}
		
		public static function configure( $option, $value ) {
			switch ( $option ) {
				case 'tainting' :
					self::$tainting = $value;
				break;
			}
		}
			
		public static function get( $url, $request = null, $options = array() ) {
			return self::request( 'GET', $url, $request, $options);
		}
		
		public static function post( $url, $request = null, $options = array() ) {
			return self::request( 'POST', $url, $request, $options);
		}
		
	}

?>