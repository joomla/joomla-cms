<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @package		JoomlaFramework
 */

//Complusoft JoomlaTeam - Support: JoomlaTeam@Complusoft.es
require_once JPATH_BASE.'/libraries/joomla/access/access.php';
require_once JPATH_BASE.'/libraries/joomla/user/user.php';
require_once JPATH_BASE.'/tests/unit/JoomlaDatabaseTestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';
/**
 * Test class for JAccess getCategoriesByGroup and getCategoriesByUser methods
 * Note that this is separate from JAccessTest class because it requires different test data
 *
 * @package		JoomlaFramework
 */

class JAccessTestGetCategories extends JoomlaDatabaseTestCase {
	/**
	 * @var		JAccess
	 * @access	protected
	 */
	protected $object;
	var $have_db = false;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp() {
		parent::setup();
		$connect = parent::getConnection();
		$assets = $this->getDataSet();
		$this->_db = JFactory::getDbo();
	}

	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');
		$dataSet->addTable('jos_assets', JPATH_BASE . '/tests/unit/stubs/jos_assets.csv');
		$dataSet->addTable('jos_categories', JPATH_BASE . '/tests/unit/stubs/jos_categories.csv');
		$dataSet->addTable('jos_usergroups', JPATH_BASE . '/tests/unit/stubs/jos_usergroups.csv');
		$dataSet->addTable('jos_user_usergroup_map', JPATH_BASE . '/tests/unit/stubs/jos_user_usergroup_map.csv');
		$dataSet->addTable('jos_users', JPATH_BASE . '/tests/unit/stubs/jos_users.csv');
		return $dataSet;
	}


		public function testGetAuthorisedCategories() {
			$user = new JUser(44);
			$notAllowed = array(22,34,64,65,66,67,75);
			$allowed = $user->getAuthorisedCategories('com_content', 'core.create');
			$this->assertEquals(array(), array_intersect($allowed, $notAllowed), 'Line: ' . __LINE__ . ' User 44 in Group 6 not allowed in these categories');
			$this->assertEquals(19, count($allowed), 'Line: ' . __LINE__ . ' User 44 in Group 6 allowed create for 19 categories');

			$user = new JUser(45);
			$notAllowed = array(22,34,64,65,66,67,75,23,68,69,70,71);
			$allowed = $user->getAuthorisedCategories('com_content', 'core.create');
			$this->assertEquals(array(), array_intersect($allowed, $notAllowed), 'Line: ' . __LINE__ . ' User 45 in Groups 5,6 not allowed in these categories');
			$this->assertEquals(14, count($allowed), 'Line: ' . __LINE__ . ' User 44 in Group 6 allowed create for 14 categories');

			$user = new JUser(46);
			$this->assertThat(
				$user->getAuthorisedCategories('com_content', 'core.create'),
				$this->equalTo(array(67)),
				'Line: ' . __LINE__ . ' User 46 in Group 2 only has create for category 67');

			$user = new JUser(42);
			$allowed = $user->getAuthorisedCategories('com_content', 'core.create');
			$this->assertEquals(26, count($allowed), 'Line: ' . __LINE__ . ' User 42 should be allowed all categories');

		}

}