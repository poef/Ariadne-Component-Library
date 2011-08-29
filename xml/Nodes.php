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
	
	class Nodes extends \ArrayObject implements NodeInterface {

		private $parentNode = null;
		public $attributes  = array();
		public $isDocumentFragment = true;
		private $nodeValue = '';
		
		public static function mergeArguments(){
			$args  = func_get_args();
			$nodes = array();
			foreach ( $args as $input ) {
				if ( is_array( $input ) || $input instanceof ar_xmlNodes ) {
					$nodes = array_merge( $nodes, (array) $input );
				} else if ($input) { // skip empty and NULL arguments
					$nodes[] = $input;
				}
			}
			return $nodes;
		}

		protected function _tryToParse( $node ) {
			return \ar\xml::tryToParse( $node );
		}
		
		public function _normalizeNodes( $nodes ) {
			$result = array();
			if ( is_array($nodes) || $nodes instanceof \Traversable ) {
				foreach ( $nodes as $node ) {
					if ( !$node instanceof Node ) {
						$node = $this->_tryToParse( $node );
					}
					if ( is_array($node) || $node instanceof \Traversable ) {
						$subnodes = $this->_normalizeNodes( $node );
						foreach ( $subnodes as $subnode ) {
							$result[] = $subnode;
						}
					} else {
						$result[] = $node;
					}
				}
			} else {
				if ( !$nodes instanceof Node ) {
					$nodes = $this->_tryToParse( $nodes );
				}
				$result[] = $nodes;
			}
			return $result;
		}
		
		public function __construct() {
			$args  = func_get_args();
			$nodes = call_user_func_array( '\ar\xml\Nodes::mergeArguments', $args );
			$nodes = $this->_normalizeNodes( $nodes );
			parent::__construct($nodes);
		}

		public function offsetSet($offset, $value) {
			if (!$value instanceof Node) {
				$value = new Node( $value );
			}
			parent::offsetSet($offset, $value);
		}
		
		private static function removeEmptyNodes( $var ) {
			return (!trim($var)=='');
		}

		public function __toString() {
			return $this->toString();
		}

		public function toString( $indentWith = null ) {
			foreach ( $this->attributes as $name => $value ) {
				$position = 0;
				foreach ( $this as $key => $node ) {
					if ($node instanceof Element) {
						$appliedValue = $this->_applyValues($value, $position);
						$node->setAttribute( $name, $appliedValue );
						$position++;
					}
				}
			}
			$result = '';
			$indent = isset($indentWith) ? $indentWith : (\ar\xml::$indenting ? \ar\xml::$indent : '');
			
			$position = 0;
			foreach ( $this as $node) {
				if ( $node instanceof Element) {
					$result .= "\n" . $node->toString($indentWith, $position);
					$position++;
				} else if ( $node instanceof Node) {
					$stringValue = (string) $node;
					if ( trim($stringValue) !== "" ) {
						$result .= $stringValue;
					}
				} else if ( $node instanceof Nodes) {
					$result .= $node->toString( $indentWith );
				} else if ( is_string($node) ) {
					$node = trim($node);
					if( $node !== "" ) {
						$result .= \ar\xml::indent( (string) $node, $indentWith);
					}
				}
			}
			return $result;
		}
		
		public function setAttributes( array $attributes, $dynamic = true ) {
			foreach ($attributes as $name => $value) {
				$this->setAttribute( $name, $value, $dynamic );
			}
			return $this;
		}

		private function _runPatterns( $value ) {
			if ($value instanceof \ar\listExpressions\Pattern) {
				$count = 0;
				foreach ( $this as $key => $node ) {
					if ($node instanceof Element) {
						$count++;
					}
				}
				$value = \ar::listExpression( $count )->pattern( $value->patterns );
			} else if ( is_array( $value ) ) {
				$newvalue = array();
				foreach ($value as $key => $subvalue ) {
					$newvalue[$key] = $this->_runPatterns( $subvalue );
				}
				$value = $newvalue;
			}
			return $value;
		}

		private function _applyValues( $value, $position = 0 ) {
			if ($value instanceof \ar\listExpressions\Expression) {
				$result = $value->item( $position );
			} else if ( is_array($value) ) {
				$result = array();
				foreach( $value as $key => $subvalue ) {
					$result[$key] = $this->_applyValues( $subvalue, $position );
				}
			} else {
				$result = $value;
			}
			return $result;
		}
		
		public function setAttribute( $name, $value, $dynamic = true ) {
			$value = $this->_runPatterns($value);
			if ($dynamic) {
				if ( isset($this->attributes[$name]) && is_array($value) && !isset($value[0]) ) {
					if (!is_array($this->attributes[$name])) {
						$this->attributes[$name] = array( $this->attributes[$name] );
					}
					$this->attributes[$name] = array_merge( (array) $this->attributes[$name], $value );
				} else {
					$this->attributes[$name] = $value;
				}
			}
			$position = 0;
			foreach ( $this as $key => $node ) {
				if ($node instanceof Element) {
					$appliedValue = $this->_applyValues($value, $position);
					$node->setAttribute( $name, $appliedValue );
					$position++;
				}
			}
			return $this;
		}
		
		public function __get( $name ) {
			switch ( $name ) {
				case 'parentNode' :
					return $this->parentNode;
				break;
				case 'firstChild' :
					return $this[0];
				break;
				case 'lastChild' :
					return $this[count($this)-1];
				break;
				case 'childNodes' :
					return $this;
				break;
				case 'nodeValue' :
					if ( count($this)==1 ) {
						return $this[0]->nodeValue;
					} else {
						$result = array();
						foreach($this as $node) {
							$result[] = $node->nodeValue;
						}
						return $result;
					}
				break;
				case 'attributes' :
					if ( count($this)==1 ) {
						return $this[0]->attributes;
					} else {
						$result = array();
						foreach($this as $node) {
							if ($node instanceof Element || $node instanceof Nodes ) {
								$result[] = $node->attributes;
							}
						}
						return $result;
					}
				break;				
				default :
					if (!isset($this->parentNode) && !$this->isDocumentFragment ) {
						$result = array();
						foreach ($this as $node) {
							if ($node instanceof Element || $node instanceof Nodes ) {
								$temp = $node->getElementsByTagName( $name, false );
								$result = array_merge( $result, (array) $temp);
							}
						}
						$result = $this->getNodeList( $result );
						$result->isDocumentFragment = false;
						return $result;
					} else {
						return $this->getElementsByTagName( $name, false );
					}
				break;
			}
		}
		
		public function __unset( $name ) {
			// e.g. unset( $xml->root->child )
			// __unset is called on $xml->root with 'child' as $name
			// so find all tags with name 'child' and remove them
			// or unset( $xml->root->child[2] )
			// 
			if (is_numeric($name)) {
				$node = $this->childNodes[$name];
				$this->removeChild($node);
			} else {
				$nodes = $this->getElementsByTagname( $name, false );
				$this->removeChild($nodes);
			}
		}
		
		public function __set( $name, $value ) {
			switch( $name ) {
				case 'parentNode' :
					$this->setParentNode($value);
				break;
				default :
					if (is_numeric($name)) {
						$node = $this->childNodes[$name];
						$this->replaceChild($node, $value);
					} else {
						switch ( $name ) {
							case 'nodeValue' : 
								foreach( $this->childNodes as $node ) {
									$node->nodeValue = $value;
								}
							break;
							default:
								$nodes = $this->getElementsByTagname( $name, false );
								$this->replaceChild($value, $nodes);
							break;
						}
					}
				break;
			}
		}
		
		public function cloneNode( $recurse = false ) {
			if (!$recurse) {
				$result = $this->getNodeList();
			} else {
				$result = clone $this;
				$result->parentNode = null;
				foreach ( $result as $pos => $el ) {
					$result[$pos] = $el->cloneNode($recurse);
				}
			}
			return $result;
		}
		
		protected function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( '\ar\xml::nodes', $params );
		}
		
		function getElementsByTagName( $name, $recurse = true ) {
			$nodeList = array(); 
			foreach ($this as $node) {
				if ( $node instanceof Element ) {				
					if ( $name == '*' || $node->tagName == $name) {
						$nodeList[] = $node;
					}
					if ($recurse) {
						$nodeList = array_merge( $nodeList, (array) $node->getElementsByTagName( $name ) );
					}
				}
			}
			$result = $this->getNodeList( $nodeList );
			$result->isDocumentFragment = false;
			return $result;
		}
		
		function getElementById( $id ) {
			if (isset($this->parentNode)) {
				return $this->parentNode->getElementById($id);
			} else {
				foreach ($this as $node ) {
					if ( $node instanceof Element ) {
						$el = $node->getElementById($id);
						if ( isset($el) ) {
							return $el;
						}
					}
				}
				return null;
			}
		}
		
		function __clearAllNodes() {
			self::__construct();
		}
		
		function setParentNode( Element $el ) {
			$this->parentNode = $el;
			foreach ($this as $node) {
				if ($node instanceof Element) {
					if ( isset($node->parentNode) ) {
						if ( $node->parentNode !== $el ) {
							$node->parentNode->removeChild($node);
						}
					} else {
						$node->parentNode = $el;
					}
				}
			}
			$this->isDocumentFragment = false;
		}
		
		function getPreviousSibling( Node $el ) {
			$pos = $this->_getPosition( $el );
			if ( $pos > 0 ) {
				return $this[ $pos - 1 ];
			} else {
				return null;
			}
		}
		
		function getNextSibling( Node $el ) {
			$pos = $this->_getLastPosition( $el );
			if ( $pos <= count( $this ) ) {
				return $this[ $pos ];
			} else {
				return null;
			}
		}
		
		function _getPosition( $el ) {
			if ( is_array($el) || $el instanceof \Traversable ) {
				return $this->_getPosition( reset($el) );
			} else {
				foreach ( $this as $pos => $node ) {
					if ( $node === $el ) {
						return $pos;
					}
				}
			}
		}

		function _getLastPosition( $el ) {
			if ( is_array($el) || $el instanceof \Traversable ) {
				return $this->_getLastPosition( end($el) );
			} else {
				foreach ( $this as $pos => $node ) {
					if ( $node === $el ) {
						return $pos+1;
					}
				}
			}
		}
		
		private function _removeChildNodes( $el ) {
			if ( isset( $this->parentNode ) ) {
				if ( is_array( $el ) || $el instanceof \Traversable ) {
					foreach ( $el as $subEl ) {
						if ( isset($subEl->parentNode) ) {
							$subEl->parentNode->removeChild( $subEl );
						}
					}
				} else {
					if ( isset($el->parentNode) ) {
						$el->parentNode->removeChild( $el );
					}
				}
			}
		}
		
		private function _setParentNodes( $el ) {
			if ( isset( $this->parentNode ) ) {
				if ( is_array( $el ) || $el instanceof \Traversable ) {
					foreach ( $el as $subEl ) {
						$this->_setParentNodes( $subEl );
					}
				} else if ( $el instanceof Node) {
					$el->__clearParentIdCache();
					$el->parentNode = $this->parentNode;
					$el->__restoreParentIdCache();
				}
			}		
		}
		
		function appendChild( $el ) {
			$this->_removeChildNodes( $el );
			$result = $this->_appendChild( $el );
			return $result;
		}
		
		private function _appendChild( $el ) {
			$this->_setParentNodes( $el );
			if ( !is_array( $el ) && !( $el instanceof \ArrayObject ) ) {
				$list = array( $el );
			} else {
				$list = (array) $el;
			}
			self::__construct( array_merge( (array) $this, $list ) );
			return $el;
		}

		function insertBefore( $el, NodeInterface $referenceEl = null ) {
			$this->_removeChildNodes( $el );
			if ( !isset($referenceEl) ) {
				return $this->_appendChild( $el );
			} else {
				$pos = $this->_getPosition( $referenceEl );
				if ( !isset($pos) ) {
					$this->_appendChild( $el );
				} else {
					$this->_setParentNodes( $el );
					if ( !is_array( $el ) ) {
						$list = array( $el );
					} else {
						$list = (array) $el;
					}
					$arr = (array) $this;
					array_splice( $arr, $pos, 0, $list );
					self::__construct( $arr );
				}
			}
			return $el;
		}
		
		function replaceChild( $el, NodeInterface $referenceEl ) {
			$this->_removeChildNodes( $el );
			$pos = $this->_getPosition( $referenceEl );
			if ( !isset($pos) ) { 
				return null;
			} else {
				$this->_setParentNodes( $el );
				if ( !is_array( $el ) ) {
					$list = array( $el );
				} else {
					$list = (array) $el;
				}
				$arr = (array) $this;
				array_splice( $arr, $pos, 0, $list ); 
				self::__construct( $arr );
				return $this->removeChild( $referenceEl );
			}
		}	

		public function removeChild( $el ) {
			// Warning: must never ever call _removeChildNodes, can be circular.
			if ( is_array( $el ) || $el instanceof \Traversable) {
				foreach( $el as $subEl ) {
					$this->removeChild( $subEl );
				}
			} else {
				$pos = $this->_getPosition( $el );
				if ( isset($pos) ) {
					$oldEl = $this[$pos];
					$arr = (array) $this;
					array_splice( $arr, $pos, 1);
					self::__construct( $arr );
					if ( isset($this->parentNode) ) {
						$oldEl->__clearParentIdCache();
						$oldEl->parentNode = null;
					}
				} else {
					return null;
				}
			}
			return $el;
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