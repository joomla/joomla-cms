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
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new JLayoutBase;
	}

	/**
	 * Tests the escape method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testEscape()
	{
		$this->assertThat(
			$this->object->escape('This is cool & fun to use!'),
			$this->equalTo('This is cool &amp; fun to use!')
		);
	}

	/**
	 * Tests the render method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testRender()
	{
		$this->assertThat(
			$this->object->render('Data'),
			$this->equalTo(''),
			'JLayoutBase::render does not render an output'
		);
	}
}
