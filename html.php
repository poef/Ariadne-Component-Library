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
	
	class html extends xml {

		public static $xhtml = false;
		public static $preserveWhiteSpace = false;
		private static $emptyTags = array(
			'input'    => 1,
			'br'       => 1,
			'hr'       => 1,
			'img'      => 1,
			'link'     => 1,
			'meta'     => 1,
			'frame'    => 1, 
			'base'     => 1,
			'basefont' => 1,
			'isindex'  => 1,
			'area'     => 1,
			'param'    => 1,
			'col'      => 1,
			'embed'    => 1
		);

		public static function configure( $option, $value ) {
			switch ($option) {
				case 'xhtml' : 
					self::$xhtml = (bool)$value;
				break;
				default:
					parent::configure($option, $value);
				break;
			}
		}
		
		public static function doctype( $type = 'strict', $quirksmode = false ) {
			if ($type) {
				$type = strtolower( $type );
				$version = '';
				switch ( $type ) {
					case 'transitional' :
					case 'frameset' :
						$version = ucfirst( $type );
					case 'strict' :
						if (self::$xhtml) {
							$version = ucfirst( $type );
							$type = '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-' . $type . '.dtd"';
						} else {
							$type = '"http://www.w3.org/TR/html4/' . $type . '.dtd"';
						}
					break;
				}
				if ($version) {
					$version = ' ' . $version;
				}
			}
			if (self::$xhtml) {
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0' . $version . '//EN"';
			} else {
				$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01' . $version . '//EN"';
			}
			if ( !$quirksmode || self::$xhtml) {
				$doctype .= ' ' . $type;
			}
			$doctype .= ">\n";
			return new html\Node($doctype);
		}
		
		public static function canHaveContent( $name ) {
			return !isset( self::$emptyTags[strtolower($name)] );
		}
		
		public static function tag() {
			$args = func_get_args();
			return call_user_func_array( '\ar\html::el', $args );
		}
		
		public static function element() {
			$args = func_get_args();
			return call_user_func_array( '\ar\html::el', $args );
		}		
	
		public static function el() {
			$args = func_get_args();
			$name = array_shift($args);
			$attributes = array();
			$childNodes = array();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !($arg instanceof xml\Nodes ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ($arg instanceof xml\Nodes) {
					$childNodes = array_merge($childNodes, (array) $arg);
				} else {
					$childNodes[] = $arg;
				}
			}
			if ( !count( $childNodes ) ) {
				$childNodes = null;
			} else {
				$childNodes = new html\Nodes( $childNodes );
			}
			return new html\Element($name, $attributes, $childNodes);
		}
			
		public static function nodes() {
			$args  = func_get_args();
			$nodes = call_user_func_array( '\ar\html\Nodes::mergeArguments', $args );
			return new html\Nodes( $nodes );
		}
		
		protected static function parseChildren( $DOMElement ) {
			$result = array();
			foreach ( $DOMElement->childNodes as $child ) {
				if ( $child instanceof \DOMCharacterData ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = new html\Node( $child->data );
					}
				} else if ( $child instanceof \DOMCdataSection ) {
					if ( self::$preserveWhiteSpace || trim( $child->data ) ) {
						$result[] = self::cdata( $child->data );
					}
				} else if ( $child instanceof \DOMNode ) {
					$result[] = self::el( $child->tagName, self::parseAttributes( $child ), self::parseChildren( $child ) );
				}
			}
			return self::nodes( $result );
		}

		public static function parse( $html ) {
			// important: parse must never return results with simple string values, but must always
			// wrap them in an ar_htmlNode, or tryToParse may get called, which will call parse, which 
			// will... etc.
			$dom = new \DOMDocument();
			$prevErrorSetting = libxml_use_internal_errors(true);
			if ( $dom->loadHTML( $html ) ) {
				$domroot = $dom->documentElement;
				if ( $domroot ) {
					$result = self::parseHead( $dom );
					$result[] = self::el( $domroot->tagName, self::parseAttributes( $domroot ), self::parseChildren( $domroot ) );
					return $result;
				}
			}
			$errors = libxml_get_errors();
			libxml_clear_errors();
			libxml_use_internal_errors( $prevErrorSetting );
			return error::raiseError( 'Incorrect html passed', exceptions::ILLEGAL_ARGUMENT, $errors );
		}

		public static function tryToParse( $html ) {
			$result = $html;
			if ( ! ($html instanceof xml\NodeInterface ) ) { // ar_xmlNodeInterface is correct, there is no ar_htmlNodeInterface
				if ($html) {
					try {
						$result = self::parse( $html );
						if ( error::isError($result) ) {
							$result = new html\Node( (string) $html );
						} else {
							$check = trim($html);
							/*
								DOMDocument::loadHTML always generates a full html document 
								so the next bit of magic tries to remove the added elements
							*/
							if (stripos($check, '<p') === 0 ) {
								$result = $result->html->body[0]->childNodes;
							} else {
								$result = $result->html->body[0];
								if ($result->firstChild->tagName=='p') {
									$result = $result->firstChild;
								}
								$result = $result->childNodes;
							}
						}
					} catch( Exception $e ) {
						$result = new html\Node( (string) $html );
					}
				} else {
					$result = new html\Node( (string) $html );
				}
			}
			return $result;
		}
	}

?>