<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void.
	 */
	protected function setUp()
	{
		$this->layoutBase = new JLayoutBase;
	}

	/**
	 * JLayoutbase set options returns a JLayoutbase instance with empty parameter.
	 *
	 * @testdox  JLayoutbase->setOptions() returns a JLayoutbase instance with empty parameter.
	 *
	 * @return  void.
	 */
	public function testJlayoutbaseSetOptionsReturnsAJlayoutbaseInstanceWithEmptyParameter()
	{
		$this->assertInstanceOf('JLayoutBase', $this->layoutBase->setOptions());
	}

	/**
	 * JLayoutbase set options returns a JLayoutbase instance with JRegistry parameter.
	 *
	 * @testdox  JLayoutbase->setOptions() returns a JLayoutbase instance with JRegistry parameter.
	 *
	 * @return  void.
	 */
	public function testJlayoutbaseSetOptionsReturnsAJlayoutbaseInstanceWithJregistryParameter()
	{
		$this->assertInstanceOf('JLayoutBase', $this->layoutBase->setOptions(new JRegistry));
	}

	/**
	 * JLayoutbase set options returns a JLayoutbase instance with an array parameter.
	 *
	 * @testdox  JLayoutbase->setOptions() returns a JLayoutbase instance with an array parameter.
	 *
	 * @return  void.
	 */
	public function testJlayoutbaseSetOptionsReturnsAJlayoutbaseInstanceWithAnArrayParameter()
	{
		$this->assertInstanceOf('JLayoutBase', $this->layoutBase->setOptions(array()));
	}

	/**
	 * Options can be configured empty.
	 *
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object when options parameter is empty.
	 *
	 * @return  void.
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectWhenOptionsParamaterIsEmpty()
	{
		$this->layoutBase->setOptions();

		$this->assertInstanceOf('JRegistry', $this->layoutBase->getOptions());
		$this->assertEmpty($this->layoutBase->getOptions()->toArray());
	}

	/**
	 * Options can be configured as an array and is converted to a JRegistry object.
	 *
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object when options parameter is an array.
	 *
	 * @return void.
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectWhenOptionsParamaterIsAnArray()
	{
		$options = array();
		$this->layoutBase->setOptions($options);

		$this->assertInstanceOf('JRegistry', $this->layoutBase->getOptions());
		$this->assertEmpty($this->layoutBase->getOptions()->toArray());
	}

	/**
	 * Get options can be configured as JRegistry object.
	 *
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object when options parameter is a JRegistry object.
	 *
	 * @return  void.
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectWhenOptionsParameterIsAJregistryObject()
	{
		$options = new JRegistry;
		$this->layoutBase->setOptions($options);

		$this->assertInstanceOf('JRegistry', $this->layoutBase->getOptions());
		$this->assertEmpty($this->layoutBase->getOptions()->toArray());
	}

	/**
	 * Options can be configured as an array and is converted to a JRegistry object.
	 *
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object value using an array.
	 *
	 * @return void.
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectValueUsingAnArray()
	{
		$options = array('option' => 'value');
		$this->layoutBase->setOptions($options);

		$this->assertEquals('value', $this->layoutBase->getOptions()->get('option'));
	}

	/**
	 * Options can be configured as an array and is converted to a JRegistry object.
	 *
	 * @testdox  JLayoutbase->getOptions() returns a JRegistry object value using JRegistry.
	 *
	 * @return void.
	 */
	public function testJlayoutbaseGetOptionsReturnsAJregistryObjectValueUsingJregistry()
	{
		$options = new JRegistry;
		$options->set('option', 'value');
		$this->layoutBase->setOptions($options);

		$this->assertEquals('value', $this->layoutBase->getOptions()->get('option'));
	}

	/**
	 * Options can be configured as an array and is converted to a JRegistry object.
	 *
	 * @testdox  JLayoutbase->resetOptions() and check options is empty.
	 *
	 * @return void.
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
	 * @return  void
	 *
	 * @since   3.0
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
	 * @return  void.
	 */
	public function testAddOneDebugMessageToTheQueue()
	{
		$messages = count($this->layoutBase->getDebugMessages());
		$this->layoutBase->addDebugMessage('Unit test');
		$this->assertCount($messages + 1, $this->layoutBase->getDebugMessages());
	}

	/**
	 * Test retrieving the debug messages.
	 *
	 * @testdox  JLayoutBase->getDebugMessages() retrieves a list of debug messages in an array.
	 *
	 * @return  void.
	 */
	public function testRetrievingTheListOfDebugMessagesIsAnArray()
	{
		$this->assertInternalType('array', $this->layoutBase->getDebugMessages());
	}

	/**
	 * Test rendering the debug messages
	 *
	 * @testdox  JLayoutBase->renderDebugMessages() returns string of messages separated by newline character.
	 *
	 * @return void.
	 */
	public function testRenderDebugMessageReturnsStringOfMessagesSeparatedByNewlineCharacter()
	{
		$this->layoutBase->addDebugMessage('Debug message 1');
		$this->assertEquals("Debug message 1", $this->layoutBase->renderDebugMessages());
		$this->layoutBase->addDebugMessage('Debug message 2');
		$this->assertEquals("Debug message 1\nDebug message 2", $this->layoutBase->renderDebugMessages());
	}

	/**
	 * Tests the render method
	 *
	 * @testdox  JLayoutBase->render() returns an empty string.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testRenderReturnsAnEmptyString()
	{
		$this->assertThat(
			$this->layoutBase->render('Data'),
			$this->equalTo(''),
			'JLayoutBase::render does not render an output'
		);
	}
}
