<?php
	require_once('./init_test.php');
	
	class TestPluggable extends UnitTestCase {
		function testAddPluginMethod() {
			ar::registerMethod( 'test', function() {
				return 'test';
			});

			$result = ar::test();

			$ar = ar();
			$result2 = $ar->test();

			$this->assertEqual( $result, 'test' );
			$this->assertEqual( $result2, 'test' );
		}

	}
?>