<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace ar;

	class xml extends \ar\Pluggable {

		public static $indenting = true;
		private static $comments = true;
		public static $indent = "\t";
		public static $strict = false;
		public static $preserveWhiteSpace = false;
		public static $namespaces = array();
		
		public static function configure( $option, $value ) {
			switch ( $option ) {
				case 'indent' :
					if ( is_bool( $value ) ) {
						self::$indenting = (bool) $value;
					} else if ( is_string( $value ) ) {
						self::$indenting = true;
						self::$indent = $value;
					} else if (!$value) {
						self::$indenting = false;
					}
				break;
				case 'comments' :
					self::$comments = (bool)$value;
				break;
				case 'strict':
					self::$strict = (bool)$value;
				break;
				case 'preserveWhiteSpace' :
					self::$preserveWhiteSpace = (bool) $value;
				break;
			}			
		}

		public function __set( $name, $value ) {
			self::configure( $name, $value );
		}
		
		public function __get( $name ) {
			if ( isset( self::${$name} ) ) {
				return self::${$name};
			}
		}
		
		public static function preamble( $version = '1.0', $encoding = 'UTF-8', $standalone = null ) {
			if ( isset($standalone) ) {
				if ( $standalone === 'false' || $standalone === false ) {
					$standalone = 'no';
				} else if ( $standalone === 'true' || $standalone === true ) {
					$standalone = 'yes';
				}
				$standalone = ' ' . self::attribute( 'standalone', $standalone );
			} else {
				$standalone = '';
			}
			return new xml\Node('<?xml version="' . self::value($version) 
				. '" encoding="' . self::value($encoding) . '"' . $standalone . ' ?>');
		}
		
		public static function comment( $comment ) {
			return ( self::$comments ? new xml\Node('<!-- '.self::value( $comment ).' -->') : '' );
		}

		public static function name( $name ) {
			$name = \ar::untaint($name, FILTER_UNSAFE_RAW);
			if (self::$strict) {
				$newname = preg_replace( '/[^-.0-9:a-z_]/isU', '', $name);
				$newname = preg_replace( '/^[^:a-z_]*/isU', '', $newname);
				//FIXME: throw an error here or something if newname !== name
				$name = $newname;
			}
			return $name;
		}

		public static function value( $value, $current = 0 ) {
			$value = \ar::untaint( $value, FILTER_UNSAFE_RAW );
			if ( is_array( $value ) ) {
				$content = '';
				foreach( $value as $subvalue ) {
					$content = rtrim($content) . ' ' . ltrim( self::value( $subvalue, $current ) );
				}
				$content = trim( $content );
			} else if ( is_bool( $value ) ) {
				$content = $value ? 'true' : 'false';
			} else if ( $value instanceof ar_listExpression ) {
				$content = self::value( $value->item( $current ) );
			} else {
				$content = str_replace( "'", "&apos;", htmlspecialchars( $value ) );
			}
			return $content;
		}
		
		public static function attribute( $name, $value, $current = 0 ) {
			if ( is_numeric( $name ) ) {					
				return self::name( $value );
			} else {
				return self::name( $name ) . '="' . self::value( $value, $current ) . '"';
			}
		}
		
		public static function attributes( $attributes, $current = 0 ) {
			$content = '';
			if ( is_array( $attributes ) ) {
				foreach( $attributes as $key => $value ) {
					$content .= ' ' . self::attribute( $key, $value, $current );
				}
			}
			return ltrim($content);
		}

		public static function cdata( $value ) {
			$value = \ar::untaint( $value, FILTER_UNSAFE_RAW );
			return new xml\Node($value, null, true);
		}
		
		public static function tag() {
			$args = func_get_args();
			return call_user_func_array( '\ar\xml::el', $args );
		}
		
		public static function element() {
			$args = func_get_args();
			return call_user_func_array( '\ar\xml::el', $args );
		}

		public static function el() {
			$args       = func_get_args();
			$name       = array_shift($args);
			$attributes = array();
			$content    = array();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !( $arg instanceof xml\Nodes ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ($arg instanceof xml\Nodes) {
					$content    = array_merge( $content, (array) $arg);
				} else {
					$content[]  = $arg;
				}
			}
			if ( !count( $content ) ) {
				$content = null;
			} else {
				$content = new xml\Nodes( $content );
			}
			return new xml\Element($name, $attributes, $content);
		}
		
		public static function indent( $content, $indent=null ) {
			if ( ( isset($indent) || self::$indenting ) && preg_match( '/^(\s*)</', $content) ) {
				if ( !isset($indent) ) {
					$indent = self::$indent;
				}
				return "\n" . preg_replace( '/^(\s*)</m', $indent . '$1<', $content ); 
			} else {
				return $content;
			}
		}
		
		public static function nodes() {
			$args  = func_get_args();
			$nodes = call_user_func_array( '\ar\xml\Nodes::mergeArguments', $args );
			return new xml\Nodes( $nodes );
		}
		
		protected static function parseAttributes( $DOMElement ) {
			// get all attributes including namespaced ones and namespaces themselves...
			// this is the best I could do given the many bugs and oversights in php's
			// DOM implementation.

			$declaredns = array();

			// this part retrieves all available namespaces on the parent
			// xpath is the only reliable way
			$x = new \DOMXPath( $DOMElement->ownerDocument );
			$p = $DOMElement->parentNode;
			if ($p && $p instanceof \DOMNode ) {
				$pns = $x->query('namespace::*', $p );
				foreach( $pns as $node ) {
					$allns[$node->localName] = $p->lookupNamespaceURI( $node->localName );
				}
			}
			// this retrieves all namespaces on the current node
			// all 'new' namespace must have been declared on this node
			$ns = $x->query('namespace::*', $DOMElement);
			foreach( $ns as $node) {
				$uri = $DOMElement->lookupNamespaceURI( $node->localName );
				if ($allns[$node->localName]!=$uri && $node->localName!='xmlns') {
					$declaredns['xmlns:'.$node->localName] = $uri;
					//$allns[$node->localName] = $uri;
				}
			}

			// finally check if the default namespace has been altered
			$dns = $DOMElement->getAttribute('xmlns');
			if ($dns) {
				$declaredns['xmlns'] = $dns;
				//$allns['{default}'] = $dns;
			}

			$result = $declaredns;

			$length = $DOMElement->attributes->length;
			for ($i=0; $i<$length; $i++) {
				$a = $DOMElement->attributes->item($i);
				if ($a->prefix) {
					$prefix = $a->prefix.':';
				}
				$result[$prefix.$a->name] = $a->value;
			}

			return $result;
		}
		
		protected static function parseChildren( $DOMElement ) {
			$result = array();
			foreach ( $DOMElement->childNodes as $child ) {
				if ( $child instanceof \DOMComment ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = new xml\Node('<!--'.$child->data.'-->');
					}
				} else if ( $child instanceof \DOMCharacterData ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = new xml\Node($child->data);
					}
				} else if ( $child instanceof \DOMCdataSection ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = self::cdata( $child->data );
					}
				} else if ( $child instanceof \DOMElement ) {
					$result[] = self::el( $child->tagName, self::parseAttributes( $child ), self::parseChildren( $child ) );
				}
			}
			return self::nodes( $result );
		}
		
		protected static function parseHead( \DOMDocument $dom ) {
			$result = self::nodes();
			if ($dom->xmlVersion && $dom->xmlEncoding) {
				$result[] = self::preamble( $dom->xmlVersion, $dom->xmlEncoding, $dom->xmlStandalone );
			}
			if ($dom->doctype) {
				$doctype = '<!DOCTYPE '.$dom->doctype->name;
				if ($dom->doctype->publicId) {
					$doctype .= ' PUBLIC "'.$dom->doctype->publicId.'"';
				}
				if ($dom->doctype->systemId) {
					$doctype .= ' "'.$dom->doctype->systemId.'"';
				}
				$doctype .= '>';
				$result[] = new xml\Node($doctype);
			}
			return $result;
		}
		
		public static function parse( $xml, $encoding = null ) {
			// important: parse must never return results with simple string values, but must always
			// wrap them in an ar_xmlNode, or tryToParse may get called, which will call parse, which 
			// will... etc.
			$dom = new \DOMDocument();
			if ( $encoding ) {
				$xml = '<?xml encoding="' . $encoding . '">' . $xml;
			}
			$prevErrorSetting = libxml_use_internal_errors(true);
			if ( $dom->loadXML( $xml ) ) {
				if ( $encoding ) {
					foreach( $dom->childNodes as $item ) {
						if ( $item->nodeType == XML_PI_NODE ) {
							$dom->removeChild( $item );
							break;
						}
					}
					$dom->encoding = $encoding;
				}
				$domroot = $dom->documentElement;
				if ( $domroot ) {
					$result = self::parseHead( $dom );
					$root = self::el( $domroot->tagName, self::parseAttributes( $domroot ), self::parseChildren( $domroot ) );
					$s = simplexml_import_dom( $dom );
					$n = $s->getDocNamespaces();
					foreach( $n as $prefix => $ns ) {
						if ($prefix) {
							$prefix = ':'.$prefix;
						}
						$root->setAttribute('xmlns'.$prefix, $ns);
					}
					$result[] = $root;
					return $result;
				}
			}
			$errors = libxml_get_errors();
			libxml_clear_errors();
			libxml_use_internal_errors( $prevErrorSetting );
			$message = 'Incorrect xml passed.';
			foreach ( $errors as $error ) {
				$message .= "\nline: ".$error->line."; column: ".$error->column."; ".$error->message;
			}
			throw new \ar\Exception( $message, exceptions::ILLEGAL_ARGUMENT );
		}
		
		public static function tryToParse( $xml, $namespaces = array() ) {
			$result = $xml;
			if ( ! ($xml instanceof xml\NodeInterface ) ) {
				if ($xml && strpos( $xml, '<' ) !== false) {
					try {
						// add a known (single) root element with all declared namespaces
						// libxml will barf on multiple root elements
						// and it will silently drop namespace prefixes not defined in the document
						$namespaces = array_merge( self::$namespaces, $namespaces );
						$root = '<arxmlroot';
						foreach ( $namespaces as $name => $uri ) {
							if ( $name === 0 ) {
								$root .= ' xmlns="';
							} else {
								$root .= ' xmlns:'.$name.'="';
							}
							$root .= htmlspecialchars( $uri ) . '"';
						}
						$root .= '>';
						$result = self::parse( $root.$xml.'</arxmlroot>' );
						$result = $result->firstChild->childNodes;
					} catch( \ar\Exception $e ) {
						$result = new xml\Node( (string) $xml );
					}
				} else {
					$result = new xml\Node( (string) $xml );
				}
			}
			return $result;
		}

		public static function registerNamespace( $name, $uri ) {
			self::$namespaces[$name] = $uri;
		}
		
	}

?>