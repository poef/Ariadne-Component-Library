<?php
	require_once('./init_test.php');
	
	class TestTainting extends UnitTestCase {

		function testTaint() {
			
			$untainted = 'Evil \' Value';
			$skipped = '42';

			$tainted = ar::taint($untainted);
			$filtered = ar::untaint($tainted);

			$skippedTainted = ar::taint($skipped);
			
			$this->assertTrue( $tainted instanceof \ar\tainting\Tainted );
			$this->assertEqual( $filtered, 'Evil &#39; Value' );
			$this->assertEqual( $skippedTainted, $skipped );
		}

	}
?>