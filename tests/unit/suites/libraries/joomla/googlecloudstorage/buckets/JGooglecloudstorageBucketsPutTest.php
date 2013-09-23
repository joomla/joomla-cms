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
		$this->options->set(
			'testLifecycle',
			array(
				array(
					"Action" => "Delete",
					"Condition" => array(
						"Age" => 30,
					),
				),
			)
		);
		$this->options->set('logBucket', "logs-bucket");
		$this->options->set('logObjectPrefix', "my-logs-enabled-bucket");
		$this->options->set('testVersioningStatus', 'testStatus');
		$this->options->set('testWebsiteConfigMainPageSuffix', 'testSuffix');
		$this->options->set('testWebsiteConfigNotFoundPage', 'testNotFoundPage');

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

	/**
	 * Tests the createLifecycleXml method
	 *
	 * @return void
	 */
	public function testCreateLifecycleXml()
	{
		$expectedResult = '<LifecycleConfiguration>
<Rule>
<Action><Delete/></Action>
<Condition>
<Age>30</Age>
</Condition>
</Rule>
</LifecycleConfiguration>';

		$this->assertThat(
			$this->object->createLifecycleXml($this->options->get("testLifecycle")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the createLoggingXml method
	 *
	 * @return void
	 */
	public function testCreateLoggingXml()
	{
		$expectedResult = '<Logging>
<LogBucket>logs-bucket</LogBucket>
<LogObjectPrefix>my-logs-enabled-bucket</LogObjectPrefix>
</Logging>';

		$this->assertThat(
			$this->object->createLoggingXml($this->options->get("logBucket"), $this->options->get("logObjectPrefix")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the createVersioningXml method
	 *
	 * @return void
	 */
	public function testCreateVersioningXml()
	{
		$expectedResult = '<VersioningConfiguration><Status>testStatus</Status></VersioningConfiguration>
';

		$this->assertThat(
			$this->object->createVersioningXml($this->options->get("testVersioningStatus")),
			$this->equalTo($expectedResult)
		);
	}

	/**
	 * Tests the createWebsiteConfigXml method
	 *
	 * @return void
	 */
	public function testCreateWebsiteConfigXml()
	{
		$expectedResult = '<WebsiteConfiguration>
<MainPageSuffix>testSuffix</MainPageSuffix>
<NotFoundPage>testNotFoundPage</NotFoundPage>
</WebsiteConfiguration>
';

		$this->assertThat(
			$this->object->createWebsiteConfigXml(
				$this->options->get("testWebsiteConfigMainPageSuffix"),
				$this->options->get("testWebsiteConfigNotFoundPage")
			),
			$this->equalTo($expectedResult)
		);
	}
}
