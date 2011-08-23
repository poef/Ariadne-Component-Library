<?php

	namespace ar\html;

	class Element extends \ar\xml\Element {
	
		public function __toString() {
			return $this->toString();
		}

		public function toString( $indent = '', $current = 0 ) {
			$indent = \ar\html::$indenting ? $indent : '';
			$result = "\n" . $indent . '<' . \ar\html::name( $this->tagName );
			if ( is_array($this->attributes) ) {
				foreach ( $this->attributes as $name => $value ) {
					$result .= \ar\html::attribute($name, $value, $current);
				}
			} else if ( is_string($this->attributes) ) {
				$result .= ltrim(' '.$this->attributes);
			}
			if ( !\ar\html::$xhtml || \ar\html::canHaveContent( $this->tagName ) ) {
				$result .= '>';
				if ( \ar\html::canHaveContent( $this->tagName ) ) {
					if ( isset($this->childNodes) && count($this->childNodes) ) {
						$result .= $this->childNodes->toString( \ar\html::$indent . $indent );
						if ( substr($result, -1) == ">") {
							$result .= "\n" . $indent;
						}
					}
					$result .= '</' . \ar\html::name( $this->tagName ) . '>';
				}
			} else {
				$result .= ' />';
			}
			return $result;
		}
		
		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( '\ar\html::nodes', $params );
		}
	}

?>