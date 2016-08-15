<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JDocumentRendererAtom.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererAtomTest extends TestCase
{
	/**
	 * @var    JDocumentRendererAtom
	 * @access protected
	 */
	protected $object;

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

		$this->markTestSkipped("Too tightly coupled to internals to be testable now");

		require_once JPATH_PLATFORM . '/joomla/application/router.php';
		require_once JPATH_PLATFORM . '/joomla/environment/request.php';
		require_once JPATH_PLATFORM . '/joomla/document/feed/feed.php';
		require_once JPATH_PLATFORM . '/joomla/environment/response.php';
		$this->saveFactoryState();

		JFactory::$application = $this->getMock(
			'JApplication',
			array(
				'get',
				'getCfg',
				'getRouter',
			)
		);

		JFactory::$application
			->expects($this->any())
			->method('getRouter')
			->will(
			$this->returnValue(new JRouter)
		);

		JFactory::$config = $this->getMock(
			'JConfig',
			array('get')
		);

		$_SERVER['REQUEST_METHOD'] = 'get';
		$input = JFactory::getApplication()->input;
		$input->set('type', 'atom');
		$this->object = new JDocumentFeed;
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['REQUEST_URI'] = '/index.php?format=feed&amp;type=atom';

		// $_SERVER['SCRIPT_NAME'] = '/index.php';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
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
			preg_replace('#(\t)*<updated>[^<]*</updated>\n#', '', $this->object->render()),
			$this->equalTo('<?xml version="1.0" encoding="utf-8"?>
<!-- generator="Joomla! 1.6 - Open Source Content Management" -->
<feed xmlns="http://www.w3.org/2005/Atom"  xml:lang="en-gb">
	<title type="text"></title>
	<subtitle type="text"></subtitle>
	<link rel="alternate" type="text/html" href="http://localhost"/>
	<id></id>
	<generator uri="http://joomla.org" version="1.6">Joomla! 1.6 - Open Source Content Management</generator>
	<link rel="self" type="application/atom+xml" href="http://localhost/index.php?format=feed&amp;type=atom"/>
	<entry>
		<title>Joomla!</title>
		<link rel="alternate" type="text/html" href="http://localhosthttps://www.joomla.org"/>
		<published>2003-01-20T14:05:41+00:00</published>
		<id>joomla</id>
		<author>
			<name>Joomla</name>
			<email>joomla@joomla.org</email>
		</author>
		<summary type="html">Joomla main site</summary>
		<content type="html">Joomla main site</content>
		<category term="CMS" />
	</entry>
</feed>
'),
			'Line:' . __LINE__ . ' The feed does not generate properly.'
		);
	}
}
