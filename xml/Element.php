<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace ar\xml;
	
	class Element extends Node {
		public    $tagName    = null;
		public    $attributes = array();
		public    $parentNode = null;
		protected $childNodes = null;
		protected $nodeValue  = '';
		protected $idCache    = array();
		public $namespaces = array();
		
		function __construct($name, $attributes = null, $childNodes = null, $parentNode = null, $namespaces = array() ) {
			$this->parentNode = $parentNode;
			list ( $xmlns, $name ) = $this->getInternalName( $name, $namespaces );
			if ( $xmlns ) {
				$name = $xmlns . ':' . $name;
			}
			$this->tagName    = $name;
			$this->childNodes = $this->getNodeList();
			$this->childNodes->setParentNode( $this );
			if ($childNodes) {
				$this->appendChild( $childNodes );
			}
			if ($attributes) {
				$this->setAttributes( $attributes );
			}
		}
		
		public function __clearParentIdCache() {
			if ( isset($this->parentNode) && count( $this->idCache ) ) {
				foreach( $this->idCache as $id => $value ) {
					$this->parentNode->__updateIdCache($id, null, $value);
				}
			}
		}
		
		public function __restoreParentIdCache() {
			if ( isset($this->parentNode) && count( $this->idCache ) ) {
				foreach( $this->idCache as $id => $value ) {
					$this->parentNode->__updateIdCache($id, $value);
				}
			}
		}

		public function __updateIdCache( $id, $el, $oldEl = null ) {
			if ( !isset($el) ) {
				// remove id cache entry
				if ( isset($this->idCache[$id]) && ($this->idCache[$id]===$oldEl) ) {
					// only remove id cache pointers to the correct element
					unset($this->idCache[$id]);
				}
			} else {
				$this->idCache[$id] = $el;
			}
			if (isset($this->parentNode)) {
				$this->parentNode->__updateIdCache($id, $el, $oldEl);
			}
		}
		
		public function getDefaultNamespace() {
			if ( $this->namespaces[0] ) {
				return $this->namespaces[0];
			} else if ( $this->parentNode ) {
				return $this->parentNode->getDefaultNamespace();
			} else {
				return '';
			}
		}
		
		public function lookupNamespace( $uri, $xmlns = '' ) {
			if ( $name = array_search( $uri, $this->namespaces ) ) {
				return $name;
			}
			if ( $this->parentNode ) {
				return $this->parentNode->lookupNamespace( $uri, $xmlns );
			}
			return $xmlns;
		}
		
		public function getInternalName( $name, $namespaces = array() ) {
			$colonPos = strpos( $name, ':' );
			if ( $colonPos !== false ) { // check namespace name
				$xmlns = $matchNs = substr( $name, 0, $colonPos );
				$name = substr( $name, $colonPos+1 );
			} else { // only check default namespace
				$xmlns = '';
				$matchNs = 0; // use this for the index in the namespaces array instead of an empty string
			}
			// first see if the xmlns given is registered locally or globally or lastly in the node itself
			if ( $namespaces[$matchNs] ) {
				$namespace = $namespaces[$matchNs];
			} else if ( \ar\xml::$namespaces[$matchNs] ) {
				$namespace = \ar\xml::$namespaces[$matchNs];
			} else {
				$namespace = '';
			}
			if ( $namespace ) { // exact match for the given namespace uri
				// find the corresponding namespace name in this document
				// if not found, return $xmlns unchanged
				$xmlns = $this->lookupNamespace( $namespace, $xmlns );
			}
			return array( $xmlns, $name, $namespace );
		}
		
		public function matchesName( $searchName, $localName, $namespaces = array() ) {
			if ( $searchName == '*' ) {
				return true;
			}
			list ( $prefix, $searchName, $namespace ) = $this->getInternalName( $searchName, $namespaces );
			$defaultNs = $this->getDefaultNamespace(); // returns the current setting for the default namespace in the document
			if ( $prefix ) {
				if ( $searchName != '*' ) {
					// exact match
					$localSubName = end( explode( ':', $localName ) );
					return ( $localName == ( $prefix . ':' . $searchName ) 
						|| ( $namespace == $defaultNs && $localName == $searchName ) 
						|| $prefix=='*' && ( $localName == $searchName || $localSubName == $searchName ) );
				} else {
					// namespace match
					$colonPosLocal = strpos( $localName, ':' );
					if ( $colonPosTag ) {
						$prefixLocal = substr( $localName, 0, $colonPosLocal );
						return $prefixLocal == $prefix;
					} else {
						return ( $defaultNs == $namespace );
					}
				}
			} else {
				// look in default namespace only
				if ( $searchName != '*' ) {
					return ( $localName == $searchName );
				} else {
					return true;
				}
			}
		}
		
		public function getAttribute( $name, $namespaces = array() ) {
			if ( $this->attributes[$name] ) {
				return $this->attributes[$name];
			} else {
				foreach ( $this->attributes as $attrName => $value ) {
					if ( $this->matchesName( $name, $attrName, $namespaces ) ) {
						return $value;
					}
				}
			}
		}

		public function setAttribute( $name, $value, $namespaces = array() ) {
			list ( $xmlns, $name ) = $this->getInternalName( $name, $namespaces );
			if ( $xmlns ) {
				$name = $xmlns . ':' . $name;
			}
			if ( $name == 'id' ) {
				$oldId = null;
				if ( isset($this->attributes['id']) ) {
					$oldId = $this->attributes['id'];
				}
			}
			if ( is_array($value) && !isset($value[0]) ) {
				// this bit of magic allows ar_xmlNodes->setAttribute to override only
				// specific attribute values, leaving others alone, by specifying a
				// non-number key.
				if ( !is_array($this->attributes[$name]) ) {
					$this->attributes[$name] = array( $this->attributes[$name] );
				}
				$this->attributes[$name] = array_merge( $this->attributes[$name], $value );
			} else {
				$this->attributes[$name] = $value;
			}
			if ( $name == 'id' ) {
				if ( isset($oldId) ) {
					$this->__updateIdCache( $oldId, null, $this );
				}
				$this->__updateIdCache( $value, $this );
			} else if ( strpos( $name, 'xmlns' ) === 0 ) {
				$nsName = substr( $name, 6 ); // strip xmlns: off
				if ( !$nsName ) {
					$nsName = 0; // default namespace
				}
				$this->namespaces[$nsName] = $value;
			}
			return $this;
		}

		public function setAttributes( $attributes ) {
			foreach ( $attributes as $name => $value ) {
				$this->setAttribute( $name, $value );
			}
			return $this;
		}		
		
		public function removeAttribute( $name ) {
			if ( isset( $this->attributes[$name] ) ) {
				unset( $this->attributes[$name] );
			}
		}
		
		public function __toString() {
			return $this->toString();
		}

		public function toString( $indent = '', $current = 0 ) {
			$indent = \ar\xml::$indenting ? $indent : '';
			$result = $indent . '<' . \ar\xml::name( $this->tagName );
			if ( is_array($this->attributes) && count( $this->attributes ) ) {
				$result .= ' ' . \ar\xml::attributes( $this->attributes, $current );
			} else if ( is_string($this->attributes) && str_length($this->attributes) ) {
				$result .= ' ' . trim( $this->attributes );
			}
			if ( $this->childNodes instanceof Nodes && count($this->childNodes) ) {
				$result .= '>';
				$result .= $this->childNodes->toString( \ar\xml::$indent . $indent );
				if ( substr($result, -1) == ">") {
					$result .= "\n" . $indent;
				}
				$result .= '</' . \ar\xml::name( $this->tagName ) . '>';
			} else {
				$result .= ' />';
			}			
			return $result;
		}

		public function __get( $name ) {
			switch( $name ) {
				case 'firstChild' :
					if (isset($this->childNodes) && count($this->childNodes)) {
						return $this->childNodes[0];
					}
				break;
				case 'lastChild' :
					if (isset($this->childNodes) && count($this->childNodes)) {
						return $this->childNodes[count($this->childNodes)-1];
					}
				break;
				case 'childNodes' :
					return $this->childNodes;
				break;
				case 'nodeValue' :
					//echo get_class($this->childNodes[0]).'('.$this->childNodes[0].')';
					if (isset($this->childNodes) && count($this->childNodes) ) {
						return $this->childNodes->nodeValue;
					}
				break;
			}
			$result = parent::__get( $name );
			if ( isset($result) ) {
				return $result;
			}
			return $this->getElementsByTagName( $name, false );
		}
		
		public function __set( $name, $value ) {
			switch ( $name ) {
				case 'previousSibling':
				case 'nextSibling':
				break;
				case 'nodeValue':
					if ( isset($this->childNodes) && count($this->childNodes) ) {
						$this->removeChild( $this->childNodes );
					}
					$this->appendChild( $value );
				break;
				case 'childNodes' :
					if ( !isset($value) ) {
						$value = $this->getNodeList();
					} else if ( !($value instanceof ar_xmlNodes) ) {
						$value = $this->getNodeList($value);
					}
					$this->childNodes->setParentNode( null );
					$this->childNodes = $value;
					$this->childNodes->setParentNode( $this );
				break;
				default:
					$nodeList = $this->__get( $name );
					$this->replaceChild( $value, $nodeList );
				break;
			}
		}
		
		public function __clone() {
			parent::__clone();
			$this->childNodes = $this->getNodeList();
		}
		
		public function cloneNode( $recurse = false ) {
			$childNodes = $this->childNodes->cloneNode( $recurse );
			$result = parent::cloneNode( $recurse );
			$result->childNodes = $childNodes;
			return $result;
		}

		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( '\ar\xml::nodes', $params );
		}

		public function getElementsByTagName( $name , $recurse = true, $namespaces = array() ) {
			if ( isset( $this->childNodes ) ) {
				return $this->childNodes->getElementsByTagName( $name, $recurse, $namespaces );
			}
		}
		
		public function getElementById( $id ) {
			if (isset($this->idCache[$id])) {
				return $this->idCache[$id];
			}
		}
		
		public function appendChild( $el ) {
			return $this->childNodes->appendChild( $el );
		}
		
		public function insertBefore( $el, $referenceEl = null ) {
			return $this->childNodes->insertBefore( $el, $referenceEl );
		}
		
		public function replaceChild( $el, $referenceEl ) {
			return $this->childNodes->replaceChild( $el, $referenceEl );
		}	

		public function removeChild( $el ) {
			return $this->childNodes->removeChild( $el );
		}

		public function bind( $nodes, $name, $type = 'string' ) {
			$b = new DataBinding( );
			return $b->bind( $nodes, $name, $type );
		}

		public function bindAsArray( $nodes, $type = 'string' ) {
			$b = new DataBinding( );
			return $b->bindAsArray( $nodes, 'list', $type)->list;
		}
		
		public function registerNamespace( $name, $uri ) {
			$this->setAttribute( 'xmlns:'.$name, $uri );
		}
		
	}
?>