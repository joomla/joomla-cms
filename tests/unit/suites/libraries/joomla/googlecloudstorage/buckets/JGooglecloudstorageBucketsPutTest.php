<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/googlecloudstorage/buckets.php';

/**
 * Test class for JGooglecloudstorage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Googlecloudstorage
 *
 * @since       ??.?
 */
class JGooglecloudstorageBucketsPutTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JGooglecloudstorageObject  Object under test.
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
		$this->options->set(
			'testCors',
			array(
				array(
					"Origins" => array(
						"http://origin1.example.com",
						"http://origin2.example.com",
					),
					"Methods" => array(
						"GET",
						"HEAD",
						"POST",
						"PUT",
						"DELETE",
					),
					"ResponseHeaders" => array(
						"x-goog-meta-foo1",
						"x-goog-meta-foo2",
					),
					"MaxAgeSec" => 1800,
				),
			)
		);

		$this->client = $this->getMock('JHttp', array('delete', 'get', 'put'));

		$this->object = new JGooglecloudstorageBucketsPut($this->options, $this->client);
	}

	/**
	 * Tests the createCorsXml method
	 *
	 * @return void
	 */
	public function testCreateCorsXml()
	{
		$expectedResult = '<CorsConfig>
<Cors>
<Origins>
<Origin>http://origin1.example.com</Origin>
<Origin>http://origin2.example.com</Origin>
</Origins>
<Methods>
<Method>GET</Method>
<Method>HEAD</Method>
<Method>POST</Method>
<Method>PUT</Method>
<Method>DELETE</Method>
</Methods>
<ResponseHeaders>
<ResponseHeader>x-goog-meta-foo1</ResponseHeader>
<ResponseHeader>x-goog-meta-foo2</ResponseHeader>
</ResponseHeaders>
<MaxAgeSec>1800</MaxAgeSec>
</Cors>
</CorsConfig>';

		$this->assertThat(
			$this->object->createCorsXml($this->options->get("testCors")),
			$this->equalTo($expectedResult)
		);
	}
}
