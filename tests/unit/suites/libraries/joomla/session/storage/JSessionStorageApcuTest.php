<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JSessionStorageApc.
 *
 * @since  1.7.0
 */
class JSessionStorageApcuTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var JSessionStorageApcu
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

		// Skip these tests if APCu isn't available.
		if (!JSessionStorageApcu::isSupported())
		{
			$this->markTestSkipped('APCu storage is not enabled on this system.');
		}

		$this->object = JSessionStorage::getInstance('apcu');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{

	}

	/**
	 * Test...
	 *
	 * @todo Implement testRead().
	 *
	 * @return void
	 */
	public function testRead()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testWrite().
	 *
	 * @return void
	 */
	public function testWrite()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testDestroy().
	 *
	 * @return void
	 */
	public function testDestroy()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testIsSupported().
	 *
	 * @return void
	 */
	public function testIsSupported()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
