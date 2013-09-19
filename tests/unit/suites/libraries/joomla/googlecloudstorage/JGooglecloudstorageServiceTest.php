<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/googlecloudstorage/service.php';

/**
 * Test class for JGooglecloudstorageService.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Googlecloudstorage
 *
 * @since       ??.?
 */
class JGooglecloudstorageServiceTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JGooglecloudstorage  Object under test.
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
		$this->object = new JGooglecloudstorage($this->options);
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
	 * Tests the magic __get method - get
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetGet()
	{
		$this->assertThat(
			$this->object->service->get,
			$this->isInstanceOf('JGooglecloudstorageServiceGet')
		);
	}
}
