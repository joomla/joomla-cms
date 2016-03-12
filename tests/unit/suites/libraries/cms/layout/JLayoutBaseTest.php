<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLayoutBase.
 */
class JLayoutBaseTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var JLayoutBase
	 */
	protected $layoutBase;

	/**
	 * Sets up the test by instantiating JLayoutBase
	 */
	protected function setUp()
	{
		$this->layoutBase = new JLayoutBase;
	}

	/**
	 * @testdox  JLayoutbase->setOptions() returns a JLayoutbase instance with empty parameter.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseSetOptionsReturnsAJlayoutbaseInstanceWithEmptyParameter()
	{
		$this->assertInstanceOf('JLayoutBase', $this->layoutBase->setOptions());
	}

	/**
	 * @testdox  JLayoutbase->setOptions() returns a JLayoutbase instance with JRegistry parameter.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseSetOptionsReturnsAJlayoutbaseInstanceWithJregistryParameter()
	{
		$this->assertInstanceOf('JLayoutBase', $this->layoutBase->setOptions(new JRegistry));
	}

	/**
	 * @testdox  JLayoutbase->setOptions() returns a JLayoutbase instance with an array parameter.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseSetOptionsReturnsAJlayoutbaseInstanceWithAnArrayParameter()
	{
		$this->assertInstanceOf('JLayoutBase', $this->layoutBase->setOptions(array()));
	}

	/**
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object when options parameter is empty.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectWhenOptionsParamaterIsEmpty()
	{
		$this->layoutBase->setOptions();

		$this->assertInstanceOf('JRegistry', $this->layoutBase->getOptions());
		$this->assertEmpty($this->layoutBase->getOptions()->toArray());
	}

	/**
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object when options parameter is an array.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectWhenOptionsParamaterIsAnArray()
	{
		$options = array();
		$this->layoutBase->setOptions($options);

		$this->assertInstanceOf('JRegistry', $this->layoutBase->getOptions());
		$this->assertEmpty($this->layoutBase->getOptions()->toArray());
	}

	/**
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object when options parameter is a JRegistry object.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectWhenOptionsParameterIsAJregistryObject()
	{
		$options = new JRegistry;
		$this->layoutBase->setOptions($options);

		$this->assertInstanceOf('JRegistry', $this->layoutBase->getOptions());
		$this->assertEmpty($this->layoutBase->getOptions()->toArray());
	}

	/**
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object value using an array.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectValueUsingAnArray()
	{
		$options = array('option' => 'value');
		$this->layoutBase->setOptions($options);

		$this->assertEquals('value', $this->layoutBase->getOptions()->get('option'));
	}

	/**
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object value using JRegistry.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectValueUsingJregistry()
	{
		$options = new JRegistry;
		$options->set('option', 'value');
		$this->layoutBase->setOptions($options);

		$this->assertEquals('value', $this->layoutBase->getOptions()->get('option'));
	}

	/**
	 * @testdox  JLayoutbase->resetOptions() and check options is empty.
	 *
	 * @since   3.3.7
	 */
	public function testJlayoutbaseResetOptionsAndCheckOptionsIsEmpty()
	{
		$options = new JRegistry;
		$options->set('option', 'value');
		$this->layoutBase->setOptions($options);

		$this->assertEmpty($this->layoutBase->resetOptions()->getOptions()->toArray());
	}

	/**
	 * Tests the escape method.
	 *
	 * @since   3.3.7
	 */
	public function testEscapingSpecialCharactersIntoHtmlEntities()
	{
		$this->assertThat(
			$this->layoutBase->escape('&'),
			$this->equalTo('&amp;'),
			'Test the ampersand is converted to HTML code'
		);

		$this->assertThat(
			$this->layoutBase->escape('"'),
			$this->equalTo('&quot;'),
			'Test the double quote is converted to HTML code'
		);

		$this->assertThat(
			$this->layoutBase->escape("'"),
			$this->equalTo("'"),
			'Test the single quote is not converted'
		);

		$this->assertThat(
			$this->layoutBase->escape("<a href='test'>Test</a>"),
			$this->equalTo("&lt;a href='test'&gt;Test&lt;/a&gt;"),
			'Test the characters <> are not converted'
		);
	}

	/**
	 * Test the adding of debug messages.
	 *
	 * @since   3.3.7
	 */
	public function testAddOneDebugMessageToTheQueue()
	{
		$messages = count($this->layoutBase->getDebugMessages());
		$this->layoutBase->addDebugMessage('Unit test');
		$this->assertCount($messages + 1, $this->layoutBase->getDebugMessages());
	}

	/**
	 * @testdox  JLayoutBase->getDebugMessages() retrieves a list of debug messages in an array.
	 *
	 * @since   3.3.7
	 */
	public function testRetrievingTheListOfDebugMessagesIsAnArray()
	{
		$this->assertInternalType('array', $this->layoutBase->getDebugMessages());
	}

	/**
	 * @testdox  JLayoutBase->renderDebugMessages() returns string of messages separated by newline character.
	 *
	 * @since   3.3.7
	 */
	public function testRenderDebugMessageReturnsStringOfMessagesSeparatedByNewlineCharacter()
	{
		$this->layoutBase->addDebugMessage('Debug message 1');
		$this->assertEquals("Debug message 1", $this->layoutBase->renderDebugMessages());
		$this->layoutBase->addDebugMessage('Debug message 2');
		$this->assertEquals("Debug message 1\nDebug message 2", $this->layoutBase->renderDebugMessages());
	}

	/**
	 * @testdox  JLayoutBase->render() returns an empty string.
	 *
	 * @since   3.3.7
	 */
	public function testRenderReturnsAnEmptyString()
	{
		$this->assertEquals('', $this->layoutBase->render('Data'), 'JLayoutBase::render does not render an output');
	}
}
