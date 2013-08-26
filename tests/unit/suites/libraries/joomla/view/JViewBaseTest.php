<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('BaseView', __DIR__ . '/stubs/tbase.php');
JLoader::register('JModelMock', __DIR__ . '/mocks/JModelMock.php');

/**
 * Tests for the JViewBase class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  View
 * @since       12.1
 */
class JViewBaseTest extends TestCase
{
	/**
	 * @var    JViewBase
	 * @since  12.1
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JViewBase::__construct
	 * @since   12.1
	 */
	public function test__construct()
	{
		$this->assertAttributeInstanceOf('JModel', 'model', $this->_instance);
	}

	/**
	 * Tests the escape method.
	 *
	 * @return  void
	 *
	 * @covers  JViewBase::escape
	 * @since   12.1
	 */
	public function testEscape()
	{
		$this->assertEquals('foo', $this->_instance->escape('foo'));
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$model = JModelMock::create($this);

		$this->_instance = new BaseView($model);
	}
}
