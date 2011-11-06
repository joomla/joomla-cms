<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/behavior.php';

/**
 * Test class for JHtmlBehavior.
 *
 * @since  11.1
 */
class JHtmlBehaviorTest extends JoomlaTestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->saveFactoryState();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * testFramework().
	 */
	public function testFramework()
	{
		// we generate a random template name so that we don't collide or hit anything
		$template = 'mytemplate'.rand(1,10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		JFactory::$application = $mock;

		JHtmlBehavior::framework();
		$this->assertArrayHasKey(
			'/media/system/js/core.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::framework failed with no arguments'
		);

		JFactory::$document->_scripts = array();
		JHtmlBehavior::framework(true);
		$this->assertArrayHasKey(
			'/media/system/js/core.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::framework failed with arg1 = true'
		);
		$this->assertArrayHasKey(
			'/media/system/js/mootools-more.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::framework failed with arg1 = true'
		);

		JFactory::$document->_scripts = array();
		JHtmlBehavior::framework(true, true);
		$this->assertArrayHasKey(
			'/media/system/js/core-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::framework failed with no arguments'
		);
		$this->assertArrayHasKey(
			'/media/system/js/mootools-core-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::framework failed with arg1 = true, arg2 = true'
		);
		$this->assertArrayHasKey(
			'/media/system/js/mootools-more-uncompressed.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::framework failed with arg1 = true, arg2 = true'
		);
	}

	/**
	 * testMootools().
	 */
	public function testMootools()
	{
		$this->markTestSkipped('This method is deprecated');
	}

	/**
	 * @todo Implement testCaption().
	 */
	public function testCaption()
	{
		// we generate a random template name so that we don't collide or hit anything
		$template = 'mytemplate'.rand(1,10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		JFactory::$application = $mock;

		JHtmlBehavior::caption();
		$this->assertArrayHasKey(
			'/media/system/js/caption.js',
			JFactory::$document->_scripts,
			'Line:' . __LINE__ . ' JHtml::caption failed with no arguments'
		);
		$this->assertThat(
			JFactory::$document->_script,
			$this->stringContains(
				"window.addEvent('load', function() {
				new JCaption('img.caption');
			});"
			)
		);

		JFactory::$document->_scripts = array();
	}

	/**
	 * @todo Implement testFormvalidation().
	 */
	public function testFormvalidation()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testSwitcher().
	 */
	public function testSwitcher()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCombobox().
	 */
	public function testCombobox()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testTooltip().
	 */
	public function testTooltip()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testModal().
	 */
	public function testModal()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testUploader().
	 */
	public function testUploader()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testTree().
	 */
	public function testTree()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testCalendar().
	 */
	public function testCalendar()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testKeepalive().
	 */
	public function testKeepalive()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}
}
