<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRackspaceStorageObject.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspaceStorageObjectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JRackspace  Object under test.
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->object = new JRackspace($this->options);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the getExtension method (with a good extension).
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function testGetExtension1()
	{
		$archiveName = "sample_archive.tar.bz2";
		$expectedResult = "tar.bz2";

		$this->assertThat(
			$this->object->storage->object->getExtension($archiveName),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the getExtension method (with a bad extension).
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function testGetExtension2()
	{
		$archiveName = "sample_archive.zip";
		$expectedResult = null;

		$this->assertThat(
			$this->object->storage->object->getExtension($archiveName),
			$this->equalTo($expectedResult)
		);
	}
}
