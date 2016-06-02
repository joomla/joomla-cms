<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Microdata
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
* Test class for JMicrodata
*
* @package     Joomla.UnitTest
* @subpackage  Microdata
* @since       3.2
*/
class JMicrodataTest extends PHPUnit_Framework_TestCase
{
	/**
	 * The default fallback Type
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $defaultType = 'Thing';

	/**
	 * Tested class handler
	 *
	 * @var    object
	 * @since  3.2
	 */
	protected $handler;

	/**
	 * Test setup
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		$this->handler = new JMicrodata;
	}

	/**
	 * Test the default settings
	 *
	 * @return  void
	 * 
	 * @since   3.2
	 */
	public function testDefaults()
	{
		$this->handler = new JMicrodata;

		// Test that the default Type is 'Thing'
		$this->assertEquals($this->handler->getType(), $this->defaultType);

		$this->assertClassHasAttribute('types', 'JMicrodata');
	}

	/**
	 * Test the setType() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testSetType()
	{
		$this->handler->setType('Article');

		// Test if the current Type is 'Article'
		$this->assertEquals($this->handler->getType(), 'Article');

		// Test if the Type fallbacks to 'Thing' Type
		$this->handler->setType('TypeThatDoesNotExist');
		$this->assertEquals($this->handler->getType(), $this->defaultType);
	}

	/**
	 * Test the fallback() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testFallback()
	{
		// Test fallback values
		$this->handler->fallback('Article', 'articleBody');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertEquals($this->handler->getFallbackProperty(), 'articleBody');

		// Test if the Fallback Property fallbacks when it isn't available in the $Type
		$this->handler->fallback('Article', 'anUnavailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Article');
		$this->assertNull($this->handler->getFallbackProperty());

		// Test if the Fallback Type fallbacks to the 'Thing' Type
		$this->handler->fallback('anUnavailableType', 'anUnavailableProperty');
		$this->assertEquals($this->handler->getFallbackType(), 'Thing');
		$this->assertNull($this->handler->getFallbackProperty());
	}

	/**
	 * Test the display() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testDisplay()
	{
		// Setup
		$content = 'anything';

		// Test display() with all null params
		$this->handler = new JMicrodata;

		$this->assertEquals($this->handler->display(), '');

		// Test if the params are reseted after the display() function
		$this->handler->setType('Article')
			->content($content)
			->property('name')
			->fallback('Thing', 'url')
			->display();

		$this->assertNull($this->handler->getFallbackProperty());
		$this->assertNull($this->handler->getFallbackType());
		$this->assertNull($this->handler->getProperty());
		$this->assertNull($this->handler->getContent());

		// Test for a simple display
		$response = $this->handler
			->property('url')
			->display();

		$this->assertEquals($response, "itemprop='url'");

		// Test for a simple display with $content
		$response = $this->handler
			->property('url')
			->content($content)
			->display();

		$this->assertEquals($response, "<span itemprop='url'>$content</span>");

		// Test for a simple display if the $content is empty ''
		$response = $this->handler->enable(true)
			->content('')
			->property('name')
			->display();

		$this->assertEquals($response, "<span itemprop='name'></span>");

		// Test for a simple 'nested' display
		$response = $this->handler
			->property('author')
			->display();

		$this->assertEquals(
			$response,
			"itemprop='author' itemscope itemtype='https://schema.org/Organization'"
		);

		// Test for a 'nested' display with $content
		$response = $this->handler
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$response,
			"<span itemprop='author' itemscope itemtype='https://schema.org/Organization'>$content</span>"
		);

		// Test for a 'nested' display with $content and $Fallback
		$response = $this->handler
			->fallback('Person', 'name')
			->property('author')
			->content($content)
			->display();

		$this->assertEquals(
			$response,
			"<span itemprop='author' itemscope itemtype='https://schema.org/Person'><span itemprop='name'>$content</span></span>"
		);

		// Test for a 'nested' display with $Fallback and without $content
		$response = $this->handler
			->fallback('Person', 'name')
			->property('author')
			->display();

		$this->assertEquals(
			$response,
			"itemprop='author' itemscope itemtype='https://schema.org/Person' itemprop='name'"
		);

		// Test for a 'meta' display without $content
		$response = $this->handler
			->property('datePublished')
			->display();

		$this->assertEquals(
			$response,
			"itemprop='datePublished'"
		);

		// Test for a 'meta' display with $content
		$content = '01 January 2011';
		$response = $this->handler
			->property('datePublished')
			->content($content)
			->display();

		$this->assertEquals(
			$response,
			"<meta itemprop='datePublished' content='$content'/>$content"
		);

		// Test for a 'meta' display with human $content and $machineContent
		$machineContent = "2011-01-01T00:00:00+00:00";
		$response = $this->handler
			->property('datePublished')
			->content($content, $machineContent)
			->display();

		$this->assertEquals(
			$response,
			"<meta itemprop='datePublished' content='$machineContent'/>$content"
		);

		// Test when if fallbacks that the library returns an empty string as specified
		$response = $this->handler
			->content('en-GB')
			->property('doesNotExist')
			->display('meta', true);

		$this->assertEquals($response, '');

		// Test if the library is disabled
		$response = $this->handler->enable(false)
			->content($content)
			->fallback('Article', 'about')
			->property('datePublished')
			->display();

		$this->assertEquals($response, $content);

		// Test if the library is disabled and if it have a $content it must return an empty string
		$response = $this->handler->enable(false)
			->content('en-GB')
			->property('inLanguage')
			->fallback('Language', 'name')
			->display('meta', true);

		$this->assertEquals($response, '');

		// Test if the params are reseted after the display() function, if the library is disabled
		$this->assertNull($this->handler->getFallbackProperty());
		$this->assertNull($this->handler->getFallbackType());
		$this->assertNull($this->handler->getProperty());
		$this->assertNull($this->handler->getContent());
	}

	/**
	 * Test the display() function when fallbacks
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testDisplayFallbacks()
	{
		// Setup
		$this->handler->enable(true)->setType('Article');
		$content = 'anything';

		// Test without $content if fallbacks, the $Property isn't available in the current Type
		$response = $this->handler
			->property('anUnavailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$response,
			"itemscope itemtype='https://schema.org/Article' itemprop='about'"
		);

		// Test with $content if fallbacks, the $Property isn't available in the current Type
		$response = $this->handler
			->content($content)
			->property('anUnavailableProperty')
			->fallback('Article', 'about')
			->display();

		$this->assertEquals(
			$response,
			"<span itemscope itemtype='https://schema.org/Article'><span itemprop='about'>$content</span></span>"
		);

		// Test if fallbacks, the $Property isn't available in the current and fallback Type
		$response = $this->handler
			->property('anUnavailableProperty')
			->fallback('Article', 'anUnavailableProperty')
			->display();

		$this->assertEquals(
			$response,
			"itemscope itemtype='https://schema.org/Article'"
		);

		// Test with $content if fallbacks, the $Property isn't available in the current $Type
		$response = $this->handler
			->content($content)
			->property('anUnavailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$response,
			"<meta itemscope itemtype='https://schema.org/Article' itemprop='datePublished' content='$content'/>"
		);

		// Test without $content if fallbacks, the $Property isn't available in the current $Type
		$response = $this->handler
			->property('anUnavailableProperty')
			->fallback('Article', 'datePublished')
			->display();

		$this->assertEquals(
			$response,
			"itemscope itemtype='https://schema.org/Article' itemprop='datePublished'"
		);
	}

	/**
	 * Test the display() function, all display types
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testDisplayTypes()
	{
		// Setup
		$type      = 'Article';
		$content   = 'anything';
		$property  = 'datePublished';
		$microdata = $this->handler;
		$microdata->enable(true)->setType($type);

		// Test Display Type: 'inline'
		$response = $microdata->content($content)
			->property($property)
			->display('inline');

		$this->assertEquals(
			$response,
			"itemprop='$property'"
		);

		// Test Display Type: 'div'
		$response = $microdata->content($content)
			->property($property)
			->display('div');

		$this->assertEquals(
			$response,
			"<div itemprop='$property'>$content</div>"
		);

		// Test Display Type: 'div' without $content
		$response = $microdata->property($property)
			->display('div');

		$this->assertEquals(
			$response,
			"<div itemprop='$property'></div>"
		);

		// Test Display Type: 'span'
		$response = $microdata->content($content)
			->property($property)
			->display('span');

		$this->assertEquals(
			$response,
			"<span itemprop='$property'>$content</span>"
		);

		// Test Display Type: 'span' without $content
		$response = $microdata
			->property($property)
			->display('span');

		$this->assertEquals(
			$response,
			"<span itemprop='$property'></span>"
		);

		// Test Display Type: 'meta'
		$response = $microdata->content($content)
			->property($property)
			->display('meta');

		$this->assertEquals(
			$response,
			"<meta itemprop='$property' content='$content'/>"
		);

		// Test Display Type: 'meta' without $content
		$response = $microdata
			->property($property)
			->display('meta');

		$this->assertEquals(
			$response,
			"<meta itemprop='$property' content=''/>"
		);
	}

	/**
	 * Test the isTypeAvailable() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsTypeAvailable()
	{
		// Test if the function returns 'true' with an available $Type
		$this->assertTrue(
			JMicrodata::isTypeAvailable('Article')
		);

		// Test if the function returns 'false' with an unavailable $Type
		$this->assertFalse(
			JMicrodata::isTypeAvailable('SomethingThatDoesNotExist')
		);
	}

	/**
	 * Test the isPropertyInType() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsPropertyInType()
	{
		// Setup
		$type = 'Article';

		// Test a $Property that is available in the $Type
		$this->assertTrue(
			JMicrodata::isPropertyInType($type, 'articleBody')
		);

		// Test an inherit $Property that is available in the $Type
		$this->assertTrue(
			JMicrodata::isPropertyInType($type, 'about')
		);

		// Test a $Property that is unavailable in the $Type
		$this->assertFalse(
			JMicrodata::isPropertyInType($type, 'aPropertyThatDoesNotExist')
		);

		// Test a Property in an unavailable Type
		$this->assertFalse(
			JMicrodata::isPropertyInType('aTypeThatDoesNotExist', 'aPropertyThatDoesNotExist')
		);
	}

	/**
	 * Test the expectedDisplayType() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testExpectedDisplayType()
	{
		// Setup
		$type = 'Article';
		$obj = new JMicrodata;

		// Test if Display Type is 'normal'
		$this->assertEquals(
			TestReflection::invoke($obj, 'getExpectedDisplayType', $type, 'articleBody'),
			'normal'
		);

		// Test if Display Type is 'nested'
		$this->assertEquals(
			TestReflection::invoke($obj, 'getExpectedDisplayType', $type, 'about'),
			'nested'
		);

		// Test if Display Type is 'meta'
		$this->assertEquals(
			TestReflection::invoke($obj, 'getExpectedDisplayType', $type, 'datePublished'),
			'meta'
		);
	}

	/**
	 * Test the displayScope() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testDisplayScope()
	{
		// Setup
		$type = 'Article';
		$this->handler->enable(true)
			->setType($type);

		// Test the displayScope() function when the library is enabled
		$this->assertEquals(
			$this->handler->displayScope(),
			"itemscope itemtype='https://schema.org/$type'"
		);

		// Test the displayScope() function when the library is disabled
		$this->assertEquals(
			$this->handler->enable(false)->displayScope(),
			""
		);
	}

	/**
	 * Test the getAvailableTypes() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetAvailableTypes()
	{
		$response = JMicrodata::getAvailableTypes();

		$this->assertGreaterThan(500, count($response));
		$this->assertNotEmpty($response);
		$this->assertTrue(in_array('Thing', $response));
	}

	/**
	 * Test the static htmlMeta() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testHtmlMeta()
	{
		$scope    = 'Article';
		$content  = 'anything';
		$property = 'datePublished';

		// Test with all params
		$this->assertEquals(
			JMicrodata::htmlMeta($content, $property, $scope),
			"<meta itemscope itemtype='https://schema.org/$scope' itemprop='$property' content='$content'/>"
		);

		// Test with the $inverse mode
		$this->assertEquals(
			JMicrodata::htmlMeta($content, $property, $scope, true),
			"<meta itemprop='$property' itemscope itemtype='https://schema.org/$scope' content='$content'/>"
		);

		// Test without the $scope
		$this->assertEquals(
			JMicrodata::htmlMeta($content, $property),
			"<meta itemprop='$property' content='$content'/>"
		);
	}

	/**
	 * Test the htmlDiv() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testHtmlDiv()
	{
		// Setup
		$scope    = 'Article';
		$content  = 'microdata';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			JMicrodata::htmlDiv($content, $property, $scope),
			"<div itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</div>"
		);

		// Test with the $inverse mode
		$this->assertEquals(
			JMicrodata::htmlDiv($content, $property, $scope, true),
			"<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>"
		);

		// Test without the $scope
		$this->assertEquals(
			JMicrodata::htmlDiv($content, $property),
			"<div itemprop='$property'>$content</div>"
		);

		// Test without the $property
		$this->assertEquals(
			JMicrodata::htmlDiv($content, $property, $scope, true),
			"<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>"
		);

		// Test without the $scope, $property
		$this->assertEquals(
			JMicrodata::htmlDiv($content),
			"<div>$content</div>"
		);
	}

	/**
	 * Test the htmlSpan() function
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testHtmlSpan()
	{
		// Setup
		$scope    = 'Article';
		$content  = 'anything';
		$property = 'about';

		// Test with all params
		$this->assertEquals(
			JMicrodata::htmlSpan($content, $property, $scope),
			"<span itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</span>"
		);

		// Test with the inverse mode
		$this->assertEquals(
			JMicrodata::htmlSpan($content, $property, $scope, true),
			"<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>"
		);

		// Test without the $scope
		$this->assertEquals(
			JMicrodata::htmlSpan($content, $property),
			"<span itemprop='$property'>$content</span>"
		);

		// Test without the $property
		$this->assertEquals(
			JMicrodata::htmlSpan($content, $property, $scope, true),
			"<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>"
		);

		// Test without the $scope, $property
		$this->assertEquals(
			JMicrodata::htmlSpan($content),
			"<span>$content</span>"
		);
	}
}
