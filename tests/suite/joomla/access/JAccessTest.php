<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/access/access.php';

/**
 * Test class for JAccess.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Access
 */
class JAccessTest extends JoomlaDatabaseTestCase
{
	/**
	 * @var    JAccess
	 * @since  11.1
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new JAccess;
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.1
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/JAccessTest.xml');
	}

	/**
	 * Tests the JAccess::getAuthorisedViewLevels method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAuthorisedViewLevels()
	{
		// Run the parent::setUp() method here to save time (since we only need to run it once)
		parent::setUp();

		if (defined('DB_NOT_AVAILABLE')) {
			$this->markTestSkipped('The database is not available');
		}

		$access = new JAccess();
		$array1 = array(
			0	=> 1,
			1   => 3
		);

		$this->assertThat(
			$access->getAuthorisedViewLevels(42),
			$this->equalTo($array1),
			'Line:'.__Line__.' Super user gets Public, Special (levels 1,3)'
		);

		$array2 = array(
			0       => 1
		);
		$this->assertThat(
			$access->getAuthorisedViewLevels(50),
			$this->equalTo($array2),
			'Line:'.__Line__.' User 50 gets Public (level 1)'
		);

		$array3 = array(
			0       => 1,
			1		=> 4
		);
		$this->assertThat(
			$access->getAuthorisedViewLevels(99),
			$this->equalTo($array3),
			'Line:'.__Line__.' User 99 gets Level 4'
		);
	}

	/**
	 * Test cases for testCheck and testCheckGroups
	 *
	 * Each test case provides
	 * - integer		userid	a user id
	 * - integer		groupid  a group id
	 * - string	    action	an action to test permission for
	 * - integer		assetid id of asset to check
	 * - mixed		true is have permission, null if no permission
	 * - string		message if fails
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	function casesCheck()
	{
		return array(
           'valid_superuser_site_login' => array(
			   42, 'core.login.site', 3, true,
               'Line:'.__LINE__.' Administrator group can login to site'
               ),
            'valid_editor_site_login' => array(
               42, 'core.login.site', 1, true,
               'Line:'.__LINE__.' Editor group'
               ),
            'valid_manager_admin_login' => array(
               44, 'core.login.admin', 1, true,
               'Line:'.__LINE__.' Administrator group can login to admin'
               ),
            'valid_manager_login' => array(
               44, 'core.admin', 1, false,
               'Line:'.__LINE__.' Administrator group cannot login to admin core'
               ),
            'super_user_admin' => array(
               42, 'core.admin', 3, true,
              'Line:'.__LINE__.' Super User group can do anything'
              ),
            'super_user_admin' => array(
               42, 'core.admin', null, true,
              'Line:'.__LINE__.' Null asset should default to 1'
              ),
            'publisher_create_banner' => array(
              43, 'core.create', 3, false,
              'Line:'.__LINE__.' Editor has explicit deny on banner create'
              ),
            'publisher_delete_banner' => array(
              43, 'core.delete', 3, false,
              'Line:'.__LINE__.' Explicit deny for editor overrides allow for publisher'
              ),
            'invalid_user_group_login' => array(
              58, 'core.login.site',3, null,
              'Line:'.__LINE__.' Invalid user and group cannot log in to site'
              ),
            'invalid_action' => array(
              42, 'complusoft',3, null,
              'Line:'.__LINE__.' Invalid action returns null permission'
              ),
            'invalid_asset_id' => array(
              42, 'core.login.site', 345, true,
              'Line:'.__LINE__.' Super user has permissions even for invalid asset id'
              ),
            'publisher_login_admin' => array(
              43, 'core.login.admin', 1, null,
              'Line:'.__LINE__.' Publisher may not log into admin'
              ),
         );
	}

	/**
	 * Tests the JAccess::check method.
	 *
	 * @param	integer		user id
	 * @param	string		action to test
	 * @param	integer		asset id
	 * @param	mixed		true if success, null if not
	 * @param	string		fail message
	 *
	 * return	void
	 *
	 * @since   11.1
	 * @dataProvider casesCheck()
	 */
	public function testCheck($userId, $action, $assetId, $result, $message)
	{
		$access = new JAccess();
		$this->assertThat(
			$access->check($userId, $action, $assetId),
			$this->equalTo($result),
			$message
		);
	}

	/**
	 * Test cases for testCheck and testCheckGroups
	 *
	 * Each test case provides
	 * - integer		userid	a user id
	 * - integer		groupid  a group id
	 * - string	    action	an action to test permission for
	 * - integer		assetid id of asset to check
	 * - mixed		true is have permission, null if no permission
	 * - string		message if fails
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	function casesCheckGroup()
	{
		return array(
           'valid_superuser_site_login' => array(
			   7, 'core.login.site', 3, true,
               'Line:'.__LINE__.' Administrator group can login to site'
               ),
            'valid_editor_site_login' => array(
               4, 'core.login.site', 1, true,
               'Line:'.__LINE__.' Editor group'
               ),
            'valid_manager_admin_login' => array(
               6, 'core.login.admin', 1, true,
               'Line:'.__LINE__.' Administrator group can login to admin'
               ),
            'valid_manager_login' => array(
               6, 'core.admin', 1, false,
               'Line:'.__LINE__.' Administrator group cannot login to admin core'
               ),
            'super_user_admin' => array(
               8, 'core.admin', 3, true,
              'Line:'.__LINE__.' Super User group can do anything'
              ),
            'null_asset' => array(
               8, 'core.admin', null, true,
              'Line:'.__LINE__.' Null asset should default to 1'
              ),
            'publisher_create_banner' => array(
              5, 'core.create', 3, false,
              'Line:'.__LINE__.' Editor has explicit deny on banner create'
              ),
            'publisher_delete_banner' => array(
              5, 'core.delete', 3, false,
              'Line:'.__LINE__.' Explicit deny for editor overrides allow for publisher'
              ),
            'invalid_user_group_login' => array(
              99, 'core.login.site',3, null,
              'Line:'.__LINE__.' Invalid user and group cannot log in to site'
              ),
            'invalid_action' => array(
              8, 'complusoft',3, null,
              'Line:'.__LINE__.' Invalid action returns null permission'
              ),
            'invalid_asset_id' => array(
              8, 'core.login.site', 123, true,
              'Line:'.__LINE__.' Super user has permissions even for invalid asset id'
              ),
            'publisher_login_admin' => array(
              5, 'core.login.admin', 1, null,
              'Line:'.__LINE__.' Publisher may not log into admin'
              ),
         );
	}

	/**
	 * Tests the JAccess::checkGroups method.
	 *
	 * @param	integer		group id
	 * @param	string		action to test
	 * @param	integer		asset id
	 * @param	mixed		true if success, null if not
	 * @param	string		fail message
	 *
	 * return	void
	 *
	 * @since   11.1
	 * @dataProvider casesCheckGroup()
	 */
	public function testCheckGroup($groupId, $action, $assetId, $result, $message)
	{
		$access = new JAccess();
		$this->assertThat(
			$access->checkGroup($groupId, $action, $assetId),
			$this->equalTo($result),
			$message);
	}

	/**
	 * Tests the JAccess::getAssetRules method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAssetRules()
	{
		if (defined('DB_NOT_AVAILABLE')) {
			$this->markTestSkipped('The database is not available');
		}

		$access = new JAccess();
		$ObjArrayJrules = $access->getAssetRules(3, True);
		$string1 = '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.admin":{"8":1,"7":1},"core.manage":{"7":1,"10":1,"6":1},"core.create":{"6":1,"4":0},"core.delete":{"6":1,"4":0,"5":1},"core.edit":{"6":1},"core.edit.state":{"6":1}}';
		$this->assertThat(
			(string)$ObjArrayJrules,
			$this->equalTo($string1),
			'Line: ' . __LINE__
		);

		$ObjArrayJrules = $access->getAssetRules(3, False);
		$string1 = '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"4":0},"core.delete":{"4":0,"5":1},"core.edit":[],"core.edit.state":[]}';
		$this->assertThat(
			(string) $ObjArrayJrules,
			$this->equalTo($string1),
			'Line: ' . __LINE__
		);

		$ObjArrayJrules = $access->getAssetRules(1550, False);
		$string1 = '[]';
		$this->assertThat(
			(string)$ObjArrayJrules,
			$this->equalTo($string1),
			'Line: ' . __LINE__
		);

		$ObjArrayJrules = $access->getAssetRules('testasset', False);
		$string1 = '[]';
		$this->assertThat(
			(string)$ObjArrayJrules,
			$this->equalTo($string1),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Tests the JAccess::getUsersByGroup method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetUsersByGroup()
	{
		if (defined('DB_NOT_AVAILABLE')) {
			$this->markTestSkipped('The database is not available');
		}

		$access = new JAccess();
		$array1 = array(
			0	=> 42
		);
		$this->assertThat(
			$array1,
			$this->equalTo($access->getUsersByGroup(8, True))
		);

		$this->assertThat(
			$array1,
			$this->equalTo($access->getUsersByGroup(7, True))
		);

		$array2 = array();
		$this->assertThat(
			$array2,
			$this->equalTo($access->getUsersByGroup(7, False))
		);
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
		if (defined('DB_NOT_AVAILABLE')) {
			$this->markTestSkipped('The database is not available');
		}

		$access = new JAccess();
		$array1 = array(
			0	=> 1,
			1	=> 6,
			2	=> 7,
			3	=> 8
		);
		$this->assertThat(
			$array1,
			$this->equalTo($access->getGroupsByUser(42, True))
		);

		$array2 = array(
			0     => 8
		);
		$this->assertThat(
			$array2,
			$this->equalTo($access->getGroupsByUser(42, False))
		);
	}

	/**
	 * Tests the JAccess::getActions method.
	 *
	 * Note, JAccess::getActions need to be changed or proxied to actually make this testable.
	 * It is currently tightly coupled to JPATH_ADMINISTRATOR.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetActions()
	{
		$this->markTestSkipped(
			'This test is skipped because the JAccess::getActions method is tightly coupled to components in the CMS.'
		);

		$access = new JAccess();
		$array1 = array(
			'name'	      => "core.admin",
                        'title'       => "JACTION_ADMIN",
                        'description' => "JACTION_ADMIN_COMPONENT_DESC"
                        );
                        $array2 = array(
			'name'	      => "core.manage",
                        'title'       => "JACTION_MANAGE",
                        'description' => "JACTION_MANAGE_COMPONENT_DESC"
                        );
                        $array3 = array(
			'name'	      => "core.create",
                        'title'       => "JACTION_CREATE",
                        'description' => "JACTION_CREATE_COMPONENT_DESC"
                        );
                        $array4 = array(
			'name'	      => "core.delete",
                        'title'       => "JACTION_DELETE",
                        'description' => "JACTION_DELETE_COMPONENT_DESC"
                        );
                        $array5 = array(
			'name'	      => "core.edit",
                        'title'       => "JACTION_EDIT",
                        'description' => "JACTION_EDIT_COMPONENT_DESC"
                        );
                        $array6 = array(
			'name'		  => "core.edit.state",
                        'title'       => "JACTION_EDITSTATE",
                        'description' => "JACTION_EDITSTATE_COMPONENT_DESC"
                        );


 		$obj = $access->getActions('com_banners', 'component');
        $arraystdClass =  (array)$obj[0];
        $this->assertThat(
        	$array1,
            $this->equalTo($arraystdClass)
        );

		$arraystdClass =  (array)$obj[1];
		$this->assertThat(
			$array2,
            $this->equalTo($arraystdClass)
        );

		$arraystdClass =  (array)$obj[2];
		$this->assertThat(
        	$array3,
            $this->equalTo($arraystdClass)
        );

		$arraystdClass =  (array)$obj[3];
		$this->assertThat(
			$array4,
			$this->equalTo($arraystdClass)
		);

		$arraystdClass =  (array)$obj[4];
		$this->assertThat(
			$array5,
			$this->equalTo($arraystdClass)
		);

		$arraystdClass =  (array)$obj[5];
		$this->assertThat(
			$array6,
			$this->equalTo($arraystdClass)
		);

		$this->assertThat(
			$array7 = array(),
			$this->equalTo($access->getActions('com_complusoft', 'component'))
		);
	}
}
