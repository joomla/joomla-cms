<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDocumentRendererRSS.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererRSSTest extends TestCase
{
	/**
	 * @var    JDocumentRendererRSS
	 */
	protected $object;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @TODO Once the coupling has been loosed, revisit and build a test harness we can use
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->backupServer = $_SERVER;

		$this->markTestSkipped("Too tightly coupled to internals to be testable now");

		$this->saveFactoryState();

		JFactory::$application = $this->getMockBuilder('JApplication')
								->setMethods(array('get', 'getCfg', 'getRouter'))
								->getMock();

		JFactory::$application
			->expects($this->any())
			->method('getRouter')
			->will(
			$this->returnValue(new JRouter)
		);

		JFactory::$config = $this->getMockBuilder('JConfig')->setMethods(array('get'))->getMock();

		$_SERVER['REQUEST_METHOD'] = 'get';
		$input = JFactory::getApplication()->input;
		$input->set('type', 'rss');
		$this->object = new JDocumentFeed;
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['SCRIPT_NAME'] = '';
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
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		$this->restoreFactoryState();
		unset($input);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * testRender method
	 *
	 * @return void
	 */
	public function testRender()
	{
		$item = new JFeedItem(
			array(
				'title' => 'Joomla!',
				'link' => 'https://www.joomla.org',
				'description' => 'Joomla main site',
				'author' => 'Joomla',
				'authorEmail' => 'joomla@joomla.org',
				'category' => 'CMS',
				'comments' => 'No comment',
				'guid' => 'joomla',
				'date' => 'Mon, 20 Jan 03 18:05:41 +0400',
				'source' => 'https://www.joomla.org'
			)
		);
		$this->object->addItem($item);
		$this->assertThat(
		// Use original 'id' and 'name' here (from XML definition of the form field)
			preg_replace('#\t\t<lastBuildDate>[^<]*</lastBuildDate>\n#', '', $this->object->render()),
			$this->equalTo('<?xml version="1.0" encoding="utf-8"?>
<!-- generator="Joomla! 1.6 - Open Source Content Management" -->
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title></title>
		<description></description>
		<link>http://localhost</link>
		<generator>Joomla! 1.6 - Open Source Content Management</generator>
		<atom:link rel="self" type="application/rss+xml" href="http://localhost/index.php?format=feed&amp;type=rss"/>
		<language>en-gb</language>
		<item>
			<title>Joomla!</title>
			<link>https://www.joomla.org</link>
			<guid isPermaLink="false">joomla</guid>
			<description><![CDATA[Joomla main site]]></description>
			<author>joomla@joomla.org (Joomla)</author>
			<category>CMS</category>
			<comments>No comment</comments>
			<pubDate>Mon, 20 Jan 2003 14:05:41 +0000</pubDate>
		</item>
	</channel>
</rss>
'),
			'Line:' . __LINE__ . ' The feed does not generate properly.'
		);
	}
}
