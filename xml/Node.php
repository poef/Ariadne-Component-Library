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
	
	class Node extends \ar\Pluggable implements NodeInterface {
		public $parentNode = null;
		private $nodeValue = '';
		public $cdata = false;
		
		public function __construct($value, $parentNode = null, $cdata = false) {
			$this->nodeValue  = $value;
			$this->parentNode = $parentNode;
			$this->cdata      = $cdata;
		}
		
		public function __toString() {
			return $this->toString();
		}

		public function toString() {
			if ($this->cdata) {
				return "<![CDATA[" . str_replace("]]>", "]]&gt;", $this->nodeValue) . "]]>";
			} else {
				return (string) $this->nodeValue;
			}
		}
		
		public function __get( $name ) {
			switch( $name ) {
				case 'previousSibling' :
					if (isset($this->parentNode)) {
						return $this->parentNode->childNodes->getPreviousSibling($this);
					}
				break;
				case 'nextSibling' :
					if (isset($this->parentNode)) {
						return $this->parentNode->childNodes->getNextSibling($this);
					}
				break;
				case 'nodeValue' :
					return $this->nodeValue;
				break;
			}
		}
		
		public function __set( $name, $value ) {
			switch ($name) {
				case 'nodeValue' :
					$this->nodeValue = $value;
				break;
			}
		}
		
		public function __isset( $name ) {
			$value = $this->__get($name);
			return isset($value);
		}
		
		public function __clone() {
			$this->parentNode = null;
		}
		
		public function cloneNode( $recurse = false ) {
			return clone($this);
		}
		
		public function __clearParentIdCache() {
		}
		
		public function __restoreParentIdCache() {
		}

	}
?>