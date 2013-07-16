<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/object.php';
require_once __DIR__ . '/stubs/JAmazons3ObjectMock.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3ObjectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3Issues  Object under test.
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
		$this->object = new JAmazons3ObjectMock($this->options);
	}

	/**
	 * Tests the fetchUrl method using an oAuth token.
	 *
	 * @return void
	 */
	public function testCreateAuthorization()
	{
		$this->options->set('api.accessKeyId', 'MyTestAccessKeyId');
		$this->options->set('api.secretAccessKey', 'MyTestSecretAccessKey');

		$url = "https://" . $this->options->get("api.url") . "/";
		$headers = array(
			"Date" => "Tue, 16 Jul 2013 19:39:06 +0300",
		);
		$expectedResult = "AWS MyTestAccessKeyId:yLA1qJ2sCHVRNPjpbRvcIA9fwGY=";

		$this->assertThat(
			$this->object->createAuthorization("GET", $url, $headers),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the fetchUrl method using an oAuth token.
	 *
	 * @return void
	 */
	public function testCreateCanonicalizedAmzHeaders()
	{
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"x-amz-acl" => "public-read",
			"content-type" => "application/x-download",
			"Content-MD5" => "4gJE4saaMU4BqNR0kLY+lw==",
			"X-Amz-Meta-ReviewedBy" => array(
				"joe@johnsmith.net",
				"jane@johnsmith.net",
			),
			"X-Amz-Meta-FileChecksum" => "0x02661779",
			"X-Amz-Meta-ChecksumAlgorithm" => "crc32",
		);

		$expectedResult = "x-amz-acl:public-read\n"
			. "x-amz-meta-checksumalgorithm:crc32\n"
			. "x-amz-meta-filechecksum:0x02661779\n"
			. "x-amz-meta-reviewedby:joe@johnsmith.net,jane@johnsmith.net\n";

		$this->assertThat(
			$this->object->createCanonicalizedAmzHeaders($headers),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the fetchUrl method using an oAuth token.
	 *
	 * @return void
	 */
	public function testCreateCanonicalizedResource()
	{
		$url = "https://jgsoc." . $this->options->get("api.url") . "/photos/puppy.jpg?acl&versionid=3&lifecycle=inf";

		$expectedResult = "/jgsoc/photos/puppy.jpg?acl&lifecycle=inf&versionid=3";

		$this->assertThat(
			$this->object->createCanonicalizedResource($url),
			$this->equalTo($expectedResult)
		);
	}
}
