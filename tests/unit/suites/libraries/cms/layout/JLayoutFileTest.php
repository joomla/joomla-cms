<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLayoutFile.
 *
 * @since  3.3.7
 */
class JLayoutFileTest extends TestCaseDatabase
{
	/**
	 * @var JLayoutFile
	 */
	protected $layoutFile;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

		$this->layoutFile = new JLayoutFile('jlayout.submenu', JPATH_TEST_STUBS);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * @testdox  Render the submenu layout file and compare output is unchanged.
	 *
	 * @since  3.3.7
	 */
	public function testRenderTheSubmenuLayoutFileAndCompareOutputIsUnchanged()
	{
		// Set the data we want to test
		$options = new stdClass;
		$options->displayMenu = true;
		$options->list = array();
		$options->displayFilters = false;
		$options->filters = array();

		// Run the test
		$this->assertStringEqualsFile(
			__DIR__ . '/output/submenu.txt',
			$this->layoutFile->render($options)
		);
	}

	/**
	 * @testdox  Render the submenu layout file with debug enabled and check there is output.
	 *
	 * @since  3.3.7
	 */
	public function testRenderTheSubmenuLayoutFileWithDebugEnabledAndCheckThereIsOutput()
	{
		// Set the data we want to test
		$options = new stdClass;
		$options->displayMenu = true;
		$options->list = array();
		$options->displayFilters = false;
		$options->filters = array();

		$this->layoutFile->setLayout('');
		$this->layoutFile->setOptions(array('debug' => true));

		$output = $this->layoutFile->render($options);

		$this->assertInternalType('string', $output);
		$this->expectOutputString("<pre></pre>");
	}

	/**
	 * @testdox  Add include files to the included path list.
	 *
	 * @since  3.3.7
	 */
	public function testAddIncludeFilesToTheIncludedPathList()
	{
		$this->layoutFile->addIncludePaths(array(JPATH_TEST_STUBS . '/jlayout/'));
		$this->layoutFile->setLayout('includepath');
		$this->layoutFile->render(array());
		$this->markTestIncomplete('Waiting for a PR form phproberto to fix the addIncludePaths');
		//$this->expectOutputString("OK");
	}

	/**
	 * @testdox  Remove include files from the path by removing a path added by the test.
	 *
	 * @since  3.3.7
	 */
	public function testRemoveIncludeFilesFromThePathByRemovingAPathAddedByTheTest()
	{
		$this->layoutFile->addIncludePaths(array(JPATH_TEST_STUBS . '/jlayout/'));
		$this->layoutFile->removeIncludePath(array(JPATH_TEST_STUBS . '/jlayout/'));
		$this->layoutFile->setLayout('includepath');
		$this->layoutFile->render(array());
		$this->markTestIncomplete('Waiting for a PR form phproberto to fix the addIncludePaths');
		//$this->expectOutputString("");
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * @testdox  Set component and check if it is set.
	 *
	 * @since  3.3.7
	 */
	public function testSetComponentAndCheckIfItIsSet()
	{
		$this->layoutFile->setComponent('com_users');
		$options = $this->layoutFile->getOptions();
		$this->assertEquals('com_users', $options->get('component'));
	}

	/**
	 * @testdox  Set invalid component and check if it is not set.
	 *
	 * @since  3.3.7
	 */
	public function testSetInvalidComponentAndCheckIfItIsNotSet()
	{
		$this->layoutFile->setComponent('com_mock');
		$options = $this->layoutFile->getOptions();
		$this->assertEquals('', $options->get('component'));
	}

	/**
	 * @testdox  Set none component to empty the component in the options and check if it is not set.
	 *
	 * @since  3.3.7
	 */
	public function testSetNoneComponentToEmptyTheComponentInTheOptionsAndCheckIfItIsNotSet()
	{
		$this->layoutFile->setComponent('none');
		$options = $this->layoutFile->getOptions();
		$this->assertEquals('', $options->get('component'));
	}

	/**
	 * @testdox  Set auto component using define and check if it is set.
	 *
	 * @since  3.3.7
	 */
	public function testSetAutoComponentUsingDefineAndCheckIfItIsSet()
	{
		define('JPATH_COMPONENT', 'com_users');
		$this->layoutFile->setComponent('auto');
		$options = $this->layoutFile->getOptions();
		$this->assertEquals('com_users', $options->get('component'));
	}

	/**
	 * @testdox  Set site client using string and check if it is set.
	 *
	 * @since  3.3.7
	 */
	public function testSetSiteClientUsingStringAndCheckIfItIsSet()
	{
		$this->layoutFile->setClient('site');
		$options = $this->layoutFile->getOptions();
		$this->assertEquals(0, $options->get('client'));
	}

	/**
	 * @testdox  Set site client using int and check if it is set.
	 *
	 * @since  3.3.7
	 */
	public function testSetSiteClientUsingIntAndCheckIfItIsSet()
	{
		$this->layoutFile->setClient(0);
		$options = $this->layoutFile->getOptions();
		$this->assertEquals(0, $options->get('client'));
	}

	/**
	 * @testdox  Set admin client using string and check if it is set.
	 *
	 * @since  3.3.7
	 */
	public function testSetAdminClientUsingStringAndCheckIfItIsSet()
	{
		$this->layoutFile->setClient('admin');
		$options = $this->layoutFile->getOptions();
		$this->assertEquals(1, $options->get('client'));
	}

	/**
	 * @testdox  Set admin client using int and check if it is set.
	 *
	 * @since  3.3.7
	 */
	public function testSetAdminClientUsingIntAndCheckIfItIsSet()
	{
		$this->layoutFile->setClient(1);
		$options = $this->layoutFile->getOptions();
		$this->assertEquals(1, $options->get('client'));
	}

	/**
	 * @testdox  Set debug and check debug is enabled.
	 *
	 * @since  3.3.7
	 */
	public function testSetDebugAndCheckDebugIsEnabled()
	{
		$this->layoutFile->setLayout('');
		$this->layoutFile->setDebug(true);

		$options = new stdClass;
		$options->displayMenu = true;
		$options->list = array();
		$options->displayFilters = false;
		$options->filters = array();

		$this->layoutFile->render($options);

		$this->expectOutputString("<pre></pre>");
	}

	/**
	 * @testdox  Render a sublayout
	 *
	 * @since  3.3.7
	 */
	public function testRenderASublayout()
	{
		$options = new stdClass;
		$options->displayMenu = true;
		$options->list = array();
		$options->displayFilters = false;
		$options->filters = array();

		$this->layoutFile->sublayout('submenu', $options);

		$this->assertStringEqualsFile(
			__DIR__ . '/output/submenu.txt',
			$this->layoutFile->render($options)
		);
	}

	/**
	 * @testdox  Render the submenu layout file using a j3x suffix and compare output is unchanged.
	 *
	 * @since  3.3.7
	 */
	public function testRenderTheSubmenuLayoutFileUsingAJ3xSuffixAndCompareOutputIsUnchanged()
	{
		$this->layoutFile->setOptions(array('suffixes' => array('j3x'), 'debug' => false));
		$this->layoutFile->setLayout('includepath');
		$this->layoutFile->addIncludePaths(array(JPATH_TEST_STUBS . '/jlayout/'));
		$output = $this->layoutFile->render(array());

		// Run the test
		$this->assertEquals("OK", $output);
	}
}
