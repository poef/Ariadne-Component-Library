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

	interface KeyValueStoreInterface {
	
		public function getvar( $name );
		
		public function putvar( $name, $value );
	
	}
?>