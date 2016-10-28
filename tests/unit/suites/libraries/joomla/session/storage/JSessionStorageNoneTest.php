<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JSessionStorageNone.
 *
 * @since  11.1
 */
class JSessionStorageNoneTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var JSessionStorageNone
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = JSessionStorage::getInstance('None');
	}

	/**
	 * Test JSessionStorageNone::Register().
	 *
	 * @return void
	 */
	public function testRegister()
	{
		$this->assertThat(
			$this->object->register(),
			$this->equalTo(null)
		);
	}
}
