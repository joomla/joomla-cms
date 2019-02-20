<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JToolbarButtonCustom.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 * @since       3.0
 */
class JToolbarButtonCustomTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Toolbar object
	 *
	 * @var    \Joomla\CMS\Toolbar\Toolbar
	 * @since  3.0
	 */
	protected $toolbar;

	/**
	 * Object under test
	 *
	 * @var    \Joomla\CMS\Toolbar\Button\CustomButton
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->toolbar = JToolbar::getInstance();
		$this->object  = $this->toolbar->loadButtonType('custom');
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->toolbar, $this->object);
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
		$html = '<div class="custom-button"><a href="#">My Custom Button</a></div>';

		$this->assertThat(
			$this->object->fetchButton('Custom', $html),
			$this->equalTo($html)
		);
	}

	/**
	 * Tests the fetchId method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @todo    Maybe move this test into JToolbarButtonTest
	 */
	public function testFetchId()
	{
		$method = new \ReflectionMethod('\Joomla\CMS\Toolbar\Button\CustomButton', 'fetchId');
		$method->setAccessible(true);
		$this->object->name('test');
		$this->assertEquals(
			'toolbar-test',
			$method->invoke($this->object)
		);
	}
}
