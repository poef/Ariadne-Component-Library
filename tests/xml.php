<?php
	require_once('./init_test.php');
	
	class TestXML extends UnitTestCase {

		var $RSSXML = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<rss xmlns:xml=\"http://www.w3.org/XML/1998/namespace\" version=\"2.0\">\n\t<channel>\n\t\t<title>Wikipedia</title>\n\t\t<link>http://www.wikipedia.org</link>\n\t\t<description>This feed notifies you of new articles on Wikipedia.</description>\n\t</channel>\n</rss>";
		var $incorrectXML = "<?xml standalone=\"false\"><rss></rss>";
		var $namespacedXML = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<testroot xmlns:foo=\"http://www.example.com/\">\n<foo:bar>something</foo:bar><bar>something else</bar><bar foo:attr=\"an attribute\">something else again</bar>\n</testroot>";
	
		function testXMLValue() {
			$value = \ar\xml::value( array( 'one', 'two', false ) );
			$value2 = \ar\xml::value( "< special \" & character ' >" );
			$value3 = \ar\xml::value( ar::taint( "< special \" & character ' >" ) );
			$this->assertEqual( $value, "one two false" );
			$this->assertEqual( $value2, "&lt; special &quot; &amp; character &apos; &gt;" );
			$this->assertEqual( $value3, $value2 );
		}

		function testXMLAttributes() {
			$attribute = \ar\xml::attribute( 'name', 'value' );
			$this->assertEqual( $attribute, 'name="value"' );
			$attribute = \ar\xml::attribute( 'wrong name', array( 'one', 'two' ) );
			$this->assertEqual( $attribute, 'wrong name="one two"' );
			\ar\xml::$strict = true;
			$attribute = \ar\xml::attribute( 'wrong name', array( 'one', 'two' ) );
			$this->assertEqual( $attribute, 'wrongname="one two"' );
			\ar\xml::$strict = false;
			$attributes = \ar\xml::attributes( array( 'attr1' => 'value1', 'attr2' => 'value2' ) );
			$this->assertEqual( $attributes, 'attr1="value1" attr2="value2"' );
		}

		function testXMLNode() {
			$preamble = \ar\xml::preamble();
			$cdata = \ar\xml::cdata('Some " value');
			$this->assertEqual( (string) $preamble, '<?xml version="1.0" encoding="UTF-8" ?>' );
			$this->assertTrue( $preamble instanceof \ar\xml\Node );
			$this->assertEqual( (string) $cdata, '<![CDATA[Some " value]]>' );
			$this->assertTrue( $cdata instanceof \ar\xml\Node );
			$comment = \ar\xml::comment('A comment');
			$this->assertEqual( (string) $comment, '<!-- A comment -->' );
			$this->assertTrue( $comment instanceof \ar\xml\Node );
			$this->assertEqual( $comment->nodeValue, '<!-- A comment -->' );
			$comment->nodeValue = '<!-- Another comment -->';
			$this->assertEqual( (string) $comment, '<!-- Another comment -->' );
		}

		function testXMLElement() {
			$el = \ar\xml::el( 'name', array( 'title' => 'a title' ) );
			$this->assertEqual( (string) $el, '<name title="a title" />' );
		}

		function testXMLNodes() {
			$nodes = \ar\xml::nodes( 
				\ar\xml::el( 'name', array( 'title' => 'a title' ) ),
				'<a>frop</a>',
				\ar\xml::nodes(
					'<!-- another string -->'
				)
			);
			$a = $nodes->a[0];
			$this->assertTrue( $a instanceof \ar\xml\Element );	// automatic parsing of xml strings
			$this->assertTrue( count( $nodes ) == 3 );			//
			$c = $nodes[2];										// nodelist is accessible as an array 
			$this->assertTrue( $c instanceof \ar\xml\Node );	// nested node lists are normalized
			$this->assertFalse( $c instanceof \ar\xml\Nodes );	// idem
			$this->assertTrue( $nodes->isDocumentFragment );
		}

		function testXMLGeneration() {
			$xml = \ar\xml::nodes(
				\ar\xml::preamble('1.0', 'utf-8', true),
				\ar\xml::el('rss', array( 'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace', 'version' => '2.0' ),
					\ar\xml::el( 'channel',
						\ar\xml::el( 'title', 'Wikipedia'),
						\ar\xml::el( 'link', 'http://www.wikipedia.org' ),
						\ar\xml::el( 'description', 'This feed notifies you of new articles on Wikipedia.' )
					)
				)
			);

			$this->assertTrue( $xml instanceof \ar\xml\Nodes );
			$this->assertEqual( (string) $xml, $this->RSSXML );

		}

		function testXMLParsing() {
			$xml = \ar\xml::parse( $this->RSSXML );

			$channelTitle = $xml->rss->channel->title->nodeValue;

			$xmlString = (string) $xml;

			try {
				$result = \ar\xml::parse( $this->incorrectXML );
			} catch( \ar\Exception $error ) {
			}
			$this->assertEqual( $channelTitle, 'Wikipedia' );
			$this->assertEqual( $xmlString, $this->RSSXML );
		
			$this->assertTrue( $error instanceof \ar\Exception );
		}

		function testNamespaceLookup() {
			$xml = \ar\xml::parse( $this->namespacedXML );
			$simplistic = $xml->testroot->{'foo:bar'};
			$xml->registerNamespace('test', 'http://www.example.com/');
			$correct = $xml->testroot->{'test:bar'};
			$this->assertTrue( $simplistic[0] instanceof \ar\xml\Element );
			$this->assertTrue( $simplistic[0]->nodeValue == 'something' );
			$this->assertTrue( $correct[0] instanceof \ar\xml\Element );
			$this->assertTrue( $correct[0]->nodeValue == 'something' );			
		}
	
		function testNamespaceCorrection() {
			$xml = \ar\xml::parse( $this->namespacedXML );
			$ns = $xml->testroot[0]->lookupNamespace('http://www.example.com/','bar');
			\ar\xml::registerNamespace('test', 'http://www.example.com/');
			$ns2 = $xml->testroot[0]->lookupNamespace('http://www.example.com/','bar');
			$xml->testroot[0]->appendChild( \ar\xml::el( 'test:bar', array( 'test:frop' => 'frip' ), 'test' ) );
			$xml->testroot[0]->appendChild('<test:bar test:frop="frup">test</test:bar>');
			$bars = $xml->testroot[0]->{'test:bar'};
			$allbars = $xml->testroot[0]->{'*:bar'};
			$this->assertTrue( $ns == $ns2 && $ns2 == 'foo' );
			$this->assertTrue( count( $bars ) == 3 );
			$this->assertTrue( count( $allbars ) == 5 );
			$this->assertTrue( $bars[1]->tagName == 'foo:bar' );
		}
		
	}
?>