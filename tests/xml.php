<?php
	require_once('./init_test.php');
	
	class TestXML extends UnitTestCase {

		var $RSSXML = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<rss xmlns:xml=\"http://www.w3.org/XML/1998/namespace\" version=\"2.0\">\n\t<channel>\n\t\t<title>Wikipedia</title>\n\t\t<link>http://www.wikipedia.org</link>\n\t\t<description>This feed notifies you of new articles on Wikipedia.</description>\n\t</channel>\n</rss>";
		var $incorrectXML = "<?xml standalone=\"false\"><rss></rss>";

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

			$xmlString = (string) $xml;

			$this->assertTrue( $xml instanceof \ar\xml\Nodes );
			$this->assertEqual( $xmlString, $this->RSSXML );

		}

		function testXMLParsing() {
			$xml = \ar\xml::parse( $this->RSSXML );

			$channelTitle = $xml->rss->channel->title->nodeValue;

			$xmlString = (string) $xml;

			$error = \ar\xml::parse( $this->incorrectXML );

			$this->assertEqual( $channelTitle, 'Wikipedia' );
			$this->assertEqual( $xmlString, $this->RSSXML );
		
			$this->assertTrue( $error instanceof \ar\Exception );
		}

	}
?>