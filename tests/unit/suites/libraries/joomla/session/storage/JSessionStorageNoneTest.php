<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JSessionStorageNone.
 *
 * @since  1.7.0
 */
class JSessionStorageNoneTest extends \PHPUnit\Framework\TestCase
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
