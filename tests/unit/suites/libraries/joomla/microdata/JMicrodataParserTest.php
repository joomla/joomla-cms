<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Microdata
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JMicrodataParser
 *
 * @package     Joomla.UnitTest
 * @subpackage  Microdata
 * @since       3.3
 */
class JMicrodataParserTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tested class handler
	 *
	 * @var    object
	 * @since  3.3
	 */
	protected $handler;

	/**
	 * Test setup
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function setUp()
	{
		$this->handler = new JMicrodataParser;
	}

	/**
	 * Test the suffix() function
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testSuffix()
	{
		/**
		 * The attribute name should not contain any uppercase letters,
		 * and must be at least one character long after the prefix "data-"
		 */
		$this->handler->suffix('');
		$this->assertNotContains('', $this->handler->getSuffix());

		// Test string input, convert to lowercase if if necessary
		$this->handler->suffix('lowercaseSuffix');
		$this->assertContains('lowercasesuffix', $this->handler->getSuffix());

		// Test array input
		$this->handler->suffix(array('su', 'ff'));
		$this->assertContains('su', $this->handler->getSuffix());
		$this->assertContains('ff', $this->handler->getSuffix());
	}

	/**
	 * Test the removeSuffix() function
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testRemoveSuffix()
	{
		$this->handler->suffix('anything');
		$this->handler->removeSuffix('anything');
		$this->assertNotContains('anything', $this->handler->getSuffix());
	}

	/**
	 * Test the getSuffix() function
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testGetSuffix()
	{
		$this->assertInternalType('array', $this->handler->getSuffix());
	}

	/**
	 * Test the parseParam() function
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testParseParam()
	{
		// Setup
		$obj    = new JMicrodataParser;
		$method = 'parseParam';

		// Test a complete params string containing the expected Type
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'Type.property.EType'),
			array(
				'type' => 'Type',
				'property' => 'property',
				'expectedType' => 'EType'
			)
		);

		// Test a complete params string
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'Type.property'),
			array(
				'type' => 'Type',
				'property' => 'property',
				'expectedType' => null
			)
		);

		// Test a params string containing the property and the expected type
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'property.EType'),
			array(
				'type' => null,
				'property' => 'property',
				'expectedType' => 'EType'
			)
		);

		// Test with only the Type param
		$this->assertEquals(
			TestReflection::invoke($obj, $method, ' Type'),
			array(
				'type' => 'Type',
				'property' => null,
				'expectedType' => null
			)
		);

		// Test with only the property param
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'property'),
			array(
				'type' => null,
				'property' => 'property',
				'expectedType' => null
			)
		);

		// Test a strange behaviour
		$this->assertEquals(
			TestReflection::invoke($obj, $method, '.Type.property'),
			array(
				'type' => null,
				'property' => null,
				'expectedType' => null
			)
		);

		// Test an empty string
		$this->assertEquals(
			TestReflection::invoke($obj, $method, ' '),
			array(
				'type' => null,
				'property' => null,
				'expectedType' => null
			)
		);
	}

	/**
	 * Test the parseParams() function
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testParseParams()
	{
		// Setup
		$obj    = new JMicrodataParser;
		$method = 'parseParams';

		// Test a complete complex case (bad semantics practice, avoid in production!)
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'Type.property.EType sProperty.EType Type FType.fProperty gProperty'),
			array(
				'setType'   => 'Type',
				'fallbacks' => array(
					'specialized' => array(
						'Type'  => array('property' => 'EType'),
						'FType' => array('fProperty' => null)
					),
					'global' => array(
						'sProperty' => 'EType',
						'gProperty' => null
					)
				)
			)
		);

		// Test with only the Type param
		$this->assertEquals(
			TestReflection::invoke($obj, $method, ' Type'),
			array(
				'setType'   => 'Type',
				'fallbacks' => array(
					'specialized' => array(),
					'global' => array()
				)
			)
		);

		// Test with only the property param
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'property'),
			array(
				'setType'   => null,
				'fallbacks' => array(
					'specialized' => array(),
					'global' => array('property' => null)
				)
			)
		);

		// Test with only the property and fallbacks params
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'property.EType FType.fProperty'),
			array(
				'setType'   => null,
				'fallbacks' => array(
					'specialized' => array(
						'FType'  => array(
							'fProperty' => null
						)
					),
					'global' => array(
						'property' => 'EType'
					)
				)
			)
		);

		// Test with only the Type and fallbacksProperty params
		$this->assertEquals(
			TestReflection::invoke($obj, $method, 'Type fProperty'),
			array(
				'setType'   => 'Type',
				'fallbacks' => array(
					'specialized' => array(),
					'global' => array(
						'fProperty' => null
					)
				)
			)
		);

		// Test a strange behaviour
		$this->assertEquals(
			TestReflection::invoke($obj, $method, ' .Type.property FType GType.  fProperty'),
			array(
				'setType'   => 'FType',
				'fallbacks' => array(
					'specialized' => array(),
					'global' => array(
						'fProperty' => null
					)
				)
			)
		);

		// Test an empty string
		$this->assertEquals(
			TestReflection::invoke($obj, $method, ' '),
			array(
				'setType'   => null,
				'fallbacks' => array(
					'specialized' => array(),
					'global' => array()
				)
			)
		);
	}

	/**
	 * Test the parse() function
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testParse()
	{
		// Setup
		$content = 'content';

		// Test a complete complex case (bad semantics practice, avoid in production!)
		$html = "<tag data-sd='articleBody Article Event.sameAs Person.award Article.url Person name'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='url'>$content</tag>"
		);

		// Test it displays the scope and set the current Type, tag parse: data-*="Article"
		$html = "<tag data-sd='Article'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemscope itemtype='https://schema.org/Article'>$content</tag>"
		);

		// Test a 'specialized' fallback, tag parse: data-*="Article.author"
		$html = "<tag data-sd='Article.author'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author'>$content</tag>"
		);

		// Test a 'specialized' fallback with an expected Type, tag parse: data-*="Article.author.Person"
		$html = "<tag data-sd='Article.author.Person'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author' itemscope itemtype='https://schema.org/Person'>$content</tag>"
		);

		// Test a 'global' property, tag parse: data-*="author"
		$html = "<tag data-sd='Article author'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author'>$content</tag>"
		);

		// Test a 'global' property with an expected Type, tag parse: data-*="author"
		$html = "<tag data-sd='author.Person'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author' itemscope itemtype='https://schema.org/Person'>$content</tag>"
		);

		// Test a strange behaviour, should set the current Type and display the scope with the last match, tag parse: data-*="Article Person"
		$html = "<tag data-sd='Article Person'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemscope itemtype='https://schema.org/Article'>$content</tag>"
		);

		// Test it displays the 'specialized' fallback instead of the 'global' fallback, tag parse: data-*="Article.articleBody description"
		$html = "<tag data-sd='Article.articleBody description'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='articleBody'>$content</tag>"
		);

		// Test check the 'global' fallbacks order, tag parse: data-*="description articleBody"
		$html = "<tag data-sd='description articleBody'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='description'>$content</tag>"
		);

		// Test self-closing tag parse: data-*="datePublished"
		$html = "<meta data-sd='Article datePublished' content='2014-01-01T00:00:00+00:00' />";
		$this->assertEquals(
			$this->handler->parse($html),
			"<meta itemprop='datePublished' content='2014-01-01T00:00:00+00:00' />"
		);

		// Test tag parse: data-*="Article.propertyDoesNotExist"
		$html = "<tag data-sd='Article.propertyDoesNotExist'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag >$content</tag>"
		);

		// Test tag parse: data-*="TypeDoesNotExist.propertyDoesNotExist"
		$html = "<tag data-sd='TypeDoesNotExist.propertyDoesNotExist'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag >$content</tag>"
		);

		// Test multiple suffix tag parse
		$this->handler->suffix('custom');
		$html = "<tag data-custom='Article.author'>$content</tag><tag data-sd='url'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author'>$content</tag><tag itemprop='url'>$content</tag>"
		);

		// Test multiple suffix tag parse, check that replaces only the first match
		$html = "<tag data-sd='Article.author' data-custom='Article.name'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag itemprop='author' data-custom='Article.name'>$content</tag>"
		);

		// Test that it doesn't parse an unregistered suffix
		$html = "<tag data-unregistered='Article.author'>$content</tag>";
		$this->assertEquals(
			$this->handler->parse($html),
			"<tag data-unregistered='Article.author'>$content</tag>"
		);
	}
}
