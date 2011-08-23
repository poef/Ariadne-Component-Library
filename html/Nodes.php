<?php

	namespace ar\html;
	
	class Nodes extends \ar\xml\Nodes {
	
		public function toString( $indentWith = null ) {
			$indent = isset($indentWith) ? $indentWith : ( 
				\ar\html::$indenting ? \ar\html::$indent : ''
			);
			return parent::toString( $indent );
		}

		public function __toString() {
			return $this->toString();
		}
		
		public function getNodeList() {
			$params = func_get_args();
			return call_user_func_array( '\ar\html::nodes', $params );
		}
		
		protected function _tryToParse( $node ) {
			return \ar\html::tryToParse( $node );
		}
		
	}
	
?>