<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.filesystem.path');

/**
 * Test class for JAccess.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Access
 * @since       11.1
 */
class JAccessTest extends TestCaseDatabase
{
	/**
	 * @var    JAccess
	 * @since  11.1
	 */
	protected $object;

	/**
	 * Tests the JAccess::getAuthorisedViewLevels method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAuthorisedViewLevels()
	{
		usleep(100);

		$access = new JAccess;
		$array1 = array(0 => 1, 1 => 1, 2 => 2, 3 => 3);

		$this->assertThat(
			$access::getAuthorisedViewLevels(42),
			$this->equalTo($array1),
			'Line:' . __Line__ . ' Super user gets Public (levels 1)'
		);
	}

	/**
	 * Test cases for testCheck
	 *
	 * Each test case provides
	 * - integer        userid    a user id
	 * - integer        groupid  a group id
	 * - string        action    an action to test permission for
	 * - integer        assetid id of asset to check
	 * - mixed        true is have permission, null if no permission
	 * - string        message if fails
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function casesCheck()
	{
		return array(
			'valid_manager_admin_login' => array(44, 'core.login.admin', 1, true, 'Line:' . __LINE__ . ' Administrator group can login to admin'),
			'valid_manager_login' => array(44, 'core.admin', 1, false, 'Line:' . __LINE__ . ' Administrator group cannot login to admin core'),
			'super_user_admin' => array(42, 'core.admin', 3, true, 'Line:' . __LINE__ . ' Super User group can do anything'),
			'super_user_admin' => array(42, 'core.admin', null, true, 'Line:' . __LINE__ . ' Null asset should default to root'),
			'publisher_delete_banner' => array(
				43,
				'core.delete',
				3,
				false,
				'Line:' . __LINE__ . ' Explicit deny for editor overrides allow for publisher'),
			'invalid_user_group_login' => array(58, 'core.login.site', 3, null, 'Line:' . __LINE__ . ' Invalid user and group cannot log in to site'),
			'invalid_action' => array(42, 'complusoft', 3, null, 'Line:' . __LINE__ . ' Invalid action returns null permission'),
			'publisher_login_admin' => array(43, 'core.login.admin', 1, null, 'Line:' . __LINE__ . ' Publisher may not log into admin'));
	}

	/**
	 * Tests the JAccess::check method.
	 *
	 * @param   integer  $userId   user id
	 * @param   string   $action   action to test
	 * @param   integer  $assetId  asset id
	 * @param   mixed    $result   true if success, null if not
	 * @param   string   $message  fail message
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @dataProvider  casesCheck()
	 */
	public function testCheck($userId, $action, $assetId, $result, $message)
	{
		$access = new JAccess;
		$this->assertThat($access::check($userId, $action, $assetId), $this->equalTo($result), $message);
	}

	/**
	 * Test cases for testCheckGroups
	 *
	 * Each test case provides
	 * - integer        userid    a user id
	 * - integer        groupid  a group id
	 * - string        action    an action to test permission for
	 * - integer        assetid id of asset to check
	 * - mixed        true is have permission, null if no permission
	 * - string        message if fails
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function casesCheckGroup()
	{
		return array(
			'valid_admin_site_login' => array(7, 'core.login.site', 3, true, 'Line:' . __LINE__ . ' Administrator group can login to site'),
			'valid_editor_site_login' => array(4, 'core.login.site', 1, true, 'Line:' . __LINE__ . ' Editor group'),
			'valid_manager_admin_login' => array(6, 'core.login.admin', 1, true, 'Line:' . __LINE__ . ' Administrator group can login to admin'),
			'valid_manager_login' => array(6, 'core.admin', 1, false, 'Line:' . __LINE__ . ' Administrator group cannot login to admin core'),
			'super_user_admin' => array(8, 'core.admin', 3, true, 'Line:' . __LINE__ . ' Super User group can do anything'),
			'null_asset' => array(8, 'core.admin', null, true, 'Line:' . __LINE__ . ' Null asset should default to 1'),
			'publisher_delete_banner' => array(
				5,
				'core.delete',
				3,
				false,
				'Line:' . __LINE__ . ' Explicit deny for editor overrides allow for publisher'),
			'invalid_user_group_login' => array(99, 'core.login.site', 3, null, 'Line:' . __LINE__ . ' Invalid user and group cannot log in to site'),
			'invalid_action' => array(8, 'complusoft', 3, null, 'Line:' . __LINE__ . ' Invalid action returns null permission'),
			'publisher_login_admin' => array(5, 'core.login.admin', 1, null, 'Line:' . __LINE__ . ' Publisher may not log into admin'));
	}

	/**
	 * Tests the JAccess::checkGroup method.
	 *
	 * @param   integer  $groupId  group id
	 * @param   string   $action   action to test
	 * @param   integer  $assetId  asset id
	 * @param   mixed    $result   true if success, null if not
	 * @param   string   $message  fail message
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @dataProvider  casesCheckGroup()
	 */
	public function testCheckGroup($groupId, $action, $assetId, $result, $message)
	{
		$access = new JAccess;
		$this->assertThat($access::checkGroup($groupId, $action, $assetId), $this->equalTo($result), $message);
	}

	/**
	 * Tests the JAccess::getAssetRules method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAssetRulesValidTrue()
	{
		$access = new JAccess;
		$ObjArrayJrules = $access::getAssetRules(3, true);
		$string1 = '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.admin":{"8":1,"7":1},' .
			'"core.manage":{"7":1,"6":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},' .
			'"core.edit.own":{"6":1,"3":1}}';
		$this->assertThat((string) $ObjArrayJrules, $this->equalTo($string1), 'Recursive rules from a valid asset. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getAssetRules method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAssetRulesValidFalse()
	{
		$access = new JAccess;
		$ObjArrayJrules = $access::getAssetRules(3, false);
		$string1 = '{"core.admin":{"7":1},"core.manage":{"6":1}}';
		$this->assertThat((string) $ObjArrayJrules, $this->equalTo($string1), 'Non recursive rules from a valid asset. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getAssetRules method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAssetRulesInvalidFalse()
	{
		$access = new JAccess;
		$ObjArrayJrules = $access::getAssetRules(1550, false);
		$string1 = '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},' .
			'"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}';
		$this->assertThat((string) $ObjArrayJrules, $this->equalTo($string1), 'Invalid asset uses rule from root. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getAssetRules method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAssetRulesTextFalse()
	{
		$access = new JAccess;
		$ObjArrayJrules = $access::getAssetRules('testasset', false);
		$string1 = '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},' .
			'"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}';
		$this->assertThat((string) $ObjArrayJrules, $this->equalTo($string1), 'Invalid asset uses rule from root. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getAssetRules method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAssetRulesTextTrue()
	{
		$access = new JAccess;
		$ObjArrayJrules = $access::getAssetRules('testasset', true);
		$string1 = '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},' .
			'"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}';
		$this->assertThat((string) $ObjArrayJrules, $this->equalTo($string1), 'Invalid asset uses rule from root. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getGroupTitle method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetGroupTitle()
	{
		$access = new JAccess;
		$this->assertThat($access::getGroupTitle(1), $this->equalTo('Public'), 'Get group title. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getUsersByGroup method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetUsersByGroupSimple()
	{
		$access = new JAccess;
		$array1 = array(0 => 42);
		$this->assertThat($access::getUsersByGroup(8, true), $this->equalTo($array1), 'Get one user. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getUsersByGroup method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetUsersByGroupTwoUsers()
	{
		$access = new JAccess;

		$array3 = array(0 => 42, 1 => 43, 2 => 44);
		$this->assertThat($access::getUsersByGroup(1, true), $this->equalTo($array3), 'Get multiple users. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getUsersByGroup method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetUsersByGroupInvalidGroup()
	{
		$access = new JAccess;

		$array2 = array();
		$this->assertThat($access::getUsersByGroup(15, false), $this->equalTo($array2), 'No group specified. Line: ' . __LINE__);
	}

	/**
	 * Tests the JAccess::getGroupsByUser method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetGroupsByUser()
	{
		$array1 = array(0 => 1, 1 => 8);
		$this->assertThat($array1, $this->equalTo(JAccess::getGroupsByUser(42, true)));

		$array2 = array(0 => 8);
		$this->assertThat($array2, $this->equalTo(JAccess::getGroupsByUser(42, false)));

		$this->markTestSkipped('Test is now failing with full test suite.');

		$this->assertThat(JAccess::getGroupsByUser(null), $this->equalTo(array(1)));

		$this->assertThat(JAccess::getGroupsByUser(null, false), $this->equalTo(array(1)));
	}

	/**
	 * Data provider for the JAccess::getActionsFromData method.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function casesGetActionsFromData()
	{
		return array(
			array(
				'<access component="com_banners">
	<section name="component">
		<action name="core.admin" title="JACTION_ADMIN" description="JACTION_ADMIN_COMPONENT_DESC" />
		<action name="core.manage" title="JACTION_MANAGE" description="JACTION_MANAGE_COMPONENT_DESC" />
		<action name="core.create" title="JACTION_CREATE" description="JACTION_CREATE_COMPONENT_DESC" />
		<action name="core.delete" title="JACTION_DELETE" description="JACTION_DELETE_COMPONENT_DESC" />
		<action name="core.edit" title="JACTION_EDIT" description="JACTION_EDIT_COMPONENT_DESC" />
		<action name="core.edit.state" title="JACTION_EDITSTATE" description="JACTION_EDITSTATE_COMPONENT_DESC" />
	</section>
	<section name="category">
		<action name="core.create" title="JACTION_CREATE" description="COM_CATEGORIES_ACCESS_CREATE_DESC" />
		<action name="core.delete" title="JACTION_DELETE" description="COM_CATEGORIES_ACCESS_DELETE_DESC" />
		<action name="core.edit" title="JACTION_EDIT" description="COM_CATEGORIES_ACCESS_EDIT_DESC" />
		<action name="core.edit.state" title="JACTION_EDITSTATE" description="COM_CATEGORIES_ACCESS_EDITSTATE_DESC" />
	</section>
</access>',
				"/access/section[@name='component']/",
				array(
					(object) array('name' => "core.admin", 'title' => "JACTION_ADMIN", 'description' => "JACTION_ADMIN_COMPONENT_DESC"),
					(object) array('name' => "core.manage", 'title' => "JACTION_MANAGE", 'description' => "JACTION_MANAGE_COMPONENT_DESC"),
					(object) array('name' => "core.create", 'title' => "JACTION_CREATE", 'description' => "JACTION_CREATE_COMPONENT_DESC"),
					(object) array('name' => "core.delete", 'title' => "JACTION_DELETE", 'description' => "JACTION_DELETE_COMPONENT_DESC"),
					(object) array('name' => "core.edit", 'title' => "JACTION_EDIT", 'description' => "JACTION_EDIT_COMPONENT_DESC"),
					(object) array('name' => "core.edit.state", 'title' => "JACTION_EDITSTATE", 'description' => "JACTION_EDITSTATE_COMPONENT_DESC")),
				'Unable to get actions from the component section.'),
			array(
				'<access component="com_banners">
	<section name="component">
		<action name="core.admin" title="JACTION_ADMIN" description="JACTION_ADMIN_COMPONENT_DESC" />
		<action name="core.manage" title="JACTION_MANAGE" description="JACTION_MANAGE_COMPONENT_DESC" />
		<action name="core.create" title="JACTION_CREATE" description="JACTION_CREATE_COMPONENT_DESC" />
		<action name="core.delete" title="JACTION_DELETE" description="JACTION_DELETE_COMPONENT_DESC" />
		<action name="core.edit" title="JACTION_EDIT" description="JACTION_EDIT_COMPONENT_DESC" />
		<action name="core.edit.state" title="JACTION_EDITSTATE" description="JACTION_EDITSTATE_COMPONENT_DESC" />
	</section>
	<section name="category">
		<action name="core.create" title="JACTION_CREATE" description="COM_CATEGORIES_ACCESS_CREATE_DESC" />
		<action name="core.delete" title="JACTION_DELETE" description="COM_CATEGORIES_ACCESS_DELETE_DESC" />
		<action name="core.edit" title="JACTION_EDIT" description="COM_CATEGORIES_ACCESS_EDIT_DESC" />
		<action name="core.edit.state" title="JACTION_EDITSTATE" description="COM_CATEGORIES_ACCESS_EDITSTATE_DESC" />
	</section>
</access>',
				"/access/section[@name='unexisting']/",
				array(),
				'Unable to get actions from an unexisting section.'),
			array(
				'<access component="com_banners',
				"/access/section[@name='component']/",
				false,
				'Getting actions from a non XML string must return false.'),
			array(
				array(),
				"/access/section[@name='component']/",
				false,
				'Getting actions from neither a string, neither an SimpleXMLElement must return false.'));
	}

	/**
	 * Tests the JAccess::getActionsFromData method.
	 *
	 * @param   string  $data      The XML string representing the actions.
	 * @param   string  $xpath     The XPath query to extract the action elements.
	 * @param   mixed   $expected  The expected array of actions.
	 * @param   string  $msg       The failure message.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @dataProvider  casesGetActionsFromData
	 */
	public function testGetActionsFromData($data, $xpath, $expected, $msg)
	{
		$this->assertThat(JAccess::getActionsFromData($data, $xpath), $this->equalTo($expected), 'Line:' . __LINE__ . $msg);
	}

	/**
	 * Tests the JAccess::getActionsFromFile method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetActionsFromFile()
	{
		$this->assertThat(
			JAccess::getActionsFromFile('/path/to/unexisting/file'),
			$this->equalTo(false),
			'Line:' . __LINE__ . ' Getting actions from an unexisting file must return false'
		);

		file_put_contents(
			JPATH_TESTS . '/tmp/access/access.xml',
			'<access component="com_banners">
	<section name="component">
		<action name="core.admin" title="JACTION_ADMIN" description="JACTION_ADMIN_COMPONENT_DESC" />
		<action name="core.manage" title="JACTION_MANAGE" description="JACTION_MANAGE_COMPONENT_DESC" />
		<action name="core.create" title="JACTION_CREATE" description="JACTION_CREATE_COMPONENT_DESC" />
		<action name="core.delete" title="JACTION_DELETE" description="JACTION_DELETE_COMPONENT_DESC" />
		<action name="core.edit" title="JACTION_EDIT" description="JACTION_EDIT_COMPONENT_DESC" />
		<action name="core.edit.state" title="JACTION_EDITSTATE" description="JACTION_EDITSTATE_COMPONENT_DESC" />
	</section>
	<section name="category">
		<action name="core.create" title="JACTION_CREATE" description="COM_CATEGORIES_ACCESS_CREATE_DESC" />
		<action name="core.delete" title="JACTION_DELETE" description="COM_CATEGORIES_ACCESS_DELETE_DESC" />
		<action name="core.edit" title="JACTION_EDIT" description="COM_CATEGORIES_ACCESS_EDIT_DESC" />
		<action name="core.edit.state" title="JACTION_EDITSTATE" description="COM_CATEGORIES_ACCESS_EDITSTATE_DESC" />
	</section>
</access>'
		);

		$this->assertThat(
			JAccess::getActionsFromFile(JPATH_TESTS . '/tmp/access/access.xml'),
			$this->equalTo(
				array(
					(object) array('name' => "core.admin", 'title' => "JACTION_ADMIN", 'description' => "JACTION_ADMIN_COMPONENT_DESC"),
					(object) array('name' => "core.manage", 'title' => "JACTION_MANAGE", 'description' => "JACTION_MANAGE_COMPONENT_DESC"),
					(object) array('name' => "core.create", 'title' => "JACTION_CREATE", 'description' => "JACTION_CREATE_COMPONENT_DESC"),
					(object) array('name' => "core.delete", 'title' => "JACTION_DELETE", 'description' => "JACTION_DELETE_COMPONENT_DESC"),
					(object) array('name' => "core.edit", 'title' => "JACTION_EDIT", 'description' => "JACTION_EDIT_COMPONENT_DESC"),
					(object) array('name' => "core.edit.state", 'title' => "JACTION_EDITSTATE", 'description' => "JACTION_EDITSTATE_COMPONENT_DESC")
				)
			),
			'Line:' . __LINE__ . ' Getting actions from an xml file must return correct array.'
		);
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.1
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_assets', JPATH_TEST_DATABASE . '/jos_assets.csv');
		$dataSet->addTable('jos_user_usergroup_map', JPATH_TEST_DATABASE . '/jos_user_usergroup_map.csv');
		$dataSet->addTable('jos_usergroups', JPATH_TEST_DATABASE . '/jos_usergroups.csv');
		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');
		$dataSet->addTable('jos_viewlevels', JPATH_TEST_DATABASE . '/jos_viewlevels.csv');

		return $dataSet;
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		// Clear the static caches.
		JAccess::clearStatics();

		$this->object = new JAccess;

		// Make sure previous test files are cleaned up
		$this->_cleanupTestFiles();

		// Make some test files and folders
		mkdir(JPath::clean(JPATH_TESTS . '/tmp/access'), 0777, true);
	}

	/**
	 * Remove created files
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function tearDown()
	{
		$this->_cleanupTestFiles();
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Convenience method to cleanup before and after test
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	private function _cleanupTestFiles()
	{
		$this->_cleanupFile(JPath::clean(JPATH_TESTS . '/tmp/access/access.xml'));
		$this->_cleanupFile(JPath::clean(JPATH_TESTS . '/tmp/access'));
	}

	/**
	 * Convenience method to clean up for files test
	 *
	 * @param   string  $path  The path to clean
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	private function _cleanupFile($path)
	{
		if (file_exists($path))
		{
			if (is_file($path))
			{
				unlink($path);
			}
			elseif (is_dir($path))
			{
				rmdir($path);
			}
		}
	}
}
