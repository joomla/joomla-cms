<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JToolbarButtonLink.
 */
class JToolbarButtonLinkTest extends TestCase
{
	/**
	 * @var    JToolbar
	 * @since  3.0
	 */
	protected $toolbar;

	/**
	 * @var    JToolbarButtonLink
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		$this->toolbar = JToolbar::getInstance();
		$this->object  = $this->toolbar->loadButtonType('link');

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the fetchButton method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testFetchButton()
	{
		$name = 'jdotorg';
		$text = 'Joomla.org';
		$url = 'http://www.joomla.org';

		$this->assertRegExp(
			'#<button onclick="location.href=\'' . preg_quote($url, '#') . '\';" class="btn btn-small">\s*'
			. '<span class="icon-' . preg_quote($name, '#') . '"></span>\s+' . preg_quote($text, '#') . '\s*'
			. '</button>\s*#',
			$this->object->fetchButton('Link', $name, $text, $url)
		);
	}

	/**
	 * Tests the fetchId method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testFetchId()
	{
		$this->assertThat(
			$this->object->fetchId('link', 'test'),
			$this->equalTo('toolbar-test')
		);
	}
}
