<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRackspacePublicFormpost.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspacePublicFormpostTest extends PHPUnit_Framework_TestCase
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
	 * Tests the createForm method.
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function testCreateForm()
	{
		$cfUrl = "http://MySampleUrl/v1/SamplePath";
		$maxFileSize = 5242880;
		$maxFileCount = 5;
		$expires = 1378460850;
		$key = "fa2bcdd1ff74782d8fece1ad7af040c011642f85";
		$expectedResult = '<form action="http://MySampleUrl/v1/SamplePath" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="max_file_size" value="5242880" />
	<input type="hidden" name="max_file_count" value="5" />
	<input type="hidden" name="expires" value="1378460850" />
	<input type="hidden" name="signature" value="9a1c5e2445a157208d9aff911ef99a102a201349" />
	<input type="file" name="file1" /><br />
	<input type="submit" />
</form>
';

		$this->assertThat(
			$this->object->public->formpost->createForm($cfUrl, $maxFileSize, $maxFileCount, $expires, $key),
			$this->equalTo($expectedResult)
		);
	}
}
