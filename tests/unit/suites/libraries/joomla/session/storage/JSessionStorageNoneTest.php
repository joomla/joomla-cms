<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once JPATH_PLATFORM . '/joomla/session/storage.php';

/**
 * Test class for JSessionStorageNone.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Session
 * @since       11.1
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
