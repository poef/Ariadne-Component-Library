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
		public $tagName     = null;
		public $attributes  = array();
		private $childNodes = null;
		public $parentNode  = null;
		private $idCache    = array();
		private $nodeValue  = '';
		
		function __construct($name, $attributes = null, $childNodes = null, $parentNode = null) {
			$this->tagName    = $name;
			$this->parentNode = $parentNode;
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

		public function __updateIdCache($id, $el, $oldEl = null) {
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
		
		function setAttributes( $attributes ) {
			foreach ( $attributes as $name => $value ) {
				$this->setAttribute( $name, $value );
			}
			return $this;
		}

		function setAttribute( $name, $value ) {
			if ( $name == 'id' ) {
				$oldId = null;
				if (isset($this->attributes['id'])) {
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
			if ($name=='id') {
				if ( isset($oldId) ) {
					$this->__updateIdCache( $oldId, null, $this );
				}
				$this->__updateIdCache($value, $this);
			}
			return $this;
		}
		
		function __toString() {
			return $this->toString();
		}

		function toString( $indent = '', $current = 0 ) {
			$indent = \ar\xml::$indenting ? $indent : '';
			$result = "\n" . $indent . '<' . \ar\xml::name( $this->tagName );
			if ( is_array($this->attributes) ) {
				foreach ( $this->attributes as $name => $value ) {
					$result .= \ar\xml::attribute($name, $value, $current);
				}
			} else if ( is_string($this->attributes) ) {
				$result .= ltrim(' '.$this->attributes);
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

		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( '\ar\xml::nodes', $params );
		}
		
		function __get( $name ) {
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
		
		function __set( $name, $value ) {
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
		
		function __clone() {
			parent::__clone();
			$this->childNodes = $this->getNodeList();
		}
		
		function cloneNode( $recurse = false ) {
			$childNodes = $this->childNodes->cloneNode( $recurse );
			$result = parent::cloneNode( $recurse );
			$result->childNodes = $childNodes;
			return $result;
		}
		
		function getElementsByTagName( $name , $recurse = true ) {
			if ( isset( $this->childNodes ) ) {
				return $this->childNodes->getElementsByTagName( $name, $recurse );
			}
		}
		
		function getElementById( $id ) {
			if (isset($this->idCache[$id])) {
				return $this->idCache[$id];
			}
		}
		
		function appendChild( $el ) {
			return $this->childNodes->appendChild( $el );
		}
		
		function insertBefore( $el, $referenceEl = null ) {
			return $this->childNodes->insertBefore( $el, $referenceEl );
		}
		
		function replaceChild( $el, $referenceEl ) {
			return $this->childNodes->replaceChild( $el, $referenceEl );
		}	

		function removeChild( $el ) {
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
		
	}
?>