<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JHtmlBatch.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlBatchTest extends TestCaseDatabase
{
	use \PHPUnit\Framework\DOMTestTrait;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$language = JLanguage::getInstance('en-GB', false);

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');
		$dataSet->addTable('jos_languages', JPATH_TEST_DATABASE . '/jos_languages.csv');
		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');
		$dataSet->addTable('jos_viewlevels', JPATH_TEST_DATABASE . '/jos_viewlevels.csv');

		return $dataSet;
	}

	/**
	 * Tests the access method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testAccess()
	{
		$result = JHtmlBatch::access();

		// Build the container to check the <label> element
		$matcher = array(
			'id'      => 'batch-access-lbl',
			'tag'     => 'label',
			'content' => 'JLIB_HTML_BATCH_ACCESS_LABEL'
		);

		$this->assertSelectEquals(
			'label#batch-access-lbl',
			'JLIB_HTML_BATCH_ACCESS_LABEL',
			1,
			$result,
			'Expected a <label> with id "batch-access-lbl"'
		);

		// Build the container to check the <select> element
		$matcher = array(
			'id'    => 'batch-access',
			'tag'   => 'select',
			'child' => array(
				'tag'        => 'option',
				'content'    => 'Public',
				'attributes' => array('value' => '1')
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <select> element with id "batch-access" containing a child <option value="1">Public</option>'
		);
	}

	/**
	 * Tests the item method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testItem()
	{
		$result = JHtmlBatch::item('com_content');

		// Build the container to check the <label> element
		$matcher = array(
			'id'      => 'batch-choose-action-lbl',
			'tag'     => 'label',
			'content' => 'JLIB_HTML_BATCH_MENU_LABEL'
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <label> with id "batch-choose-action-lbl"'
		);

		// Build the container to check the <div> element
		$matcher = array(
			'id'    => 'batch-choose-action',
			'tag'   => 'div',
			'child' => array(
				'id'    => 'batch-category-id',
				'tag'   => 'select',
				'child' => array(
					'tag'=> 'option',
					'content'    => '- - - Modules',
					'attributes' => array('value' => '22'),
				)
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected <div id="batch-choose-action"> containing child <select id="batch-category-id"> with <option value="22">- - - Modules</option>'
		);
	}

	/**
	 * Tests the language method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testLanguage()
	{
		$result = JHtmlBatch::language();

		// Build the container to check the <label> element
		$matcher = array(
			'id'      => 'batch-language-lbl',
			'tag'     => 'label',
			'content' => 'JLIB_HTML_BATCH_LANGUAGE_LABEL'
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <label> with id "batch-language-lbl"'
		);

		// Build the container to check the <select> element
		$matcher = array(
			'id'    => 'batch-language-id',
			'tag'   => 'select',
			'child' => array(
				'tag'        => 'option',
				'content'    => 'English (en-GB)',
				'attributes' => array('value' => 'en-GB')
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <select> element with id "batch-language-id" containing a child <option value="en-GB">English (UK)</option>'
		);
	}

	/**
	 * Tests the user method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUser()
	{
		$result = JHtmlBatch::user(true);

		// Build the container to check the <label> element
		$matcher = array(
			'id'      => 'batch-user-lbl',
			'tag'     => 'label',
			'content' => 'JLIB_HTML_BATCH_USER_LABEL'
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <label> with id "batch-user-lbl"'
		);

		// Build the container to check the <select> element
		$matcher = array(
			'id'    => 'batch-user-id',
			'tag'   => 'select',
			'child' => array(
				'tag'        => 'option',
				'content'    => 'Super User',
				'attributes' => array('value' => '42')
			)
		);

		$this->assertTag(
			$matcher,
			$result,
			'Expected a <select> element with id "batch-user-id" containing a child <option value="42">Super User</option>'
		);
	}
}
