<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

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