<?php

	namespace ar\events;
	
	class Listener {
		private $capture = false;
		private $path = '';
		private $name = '';
		private $id = null;
		private $stack = null;
		
		public function __construct( $name, $path, $capture, $id, $stack = null ) {
			$this->name = $name;
			$this->path = $path;
			$this->capture = $capture;
			$this->id = $id;
			$this->stack = $stack;
		}
		
		public function remove() {
			if ( isset($this->id) ) {
				$this->stack->removeListener( $this->name, $this->path, $this->capture, $this->id );
			}
		}
		
		/* FIXME: add a add() method, which re-adds the listener, potentially as last in the list */
	}
?>