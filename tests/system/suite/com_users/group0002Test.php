<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests creating a new group and assigning it to a specified parent.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Group0002Test extends SeleniumJoomlaTestCase
{
	function testCreateNewGroups()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$saltGroup = mt_rand();

		$groupParent = 'Public';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Manager';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Administrator';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Super Users';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Registered';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Author';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Editor';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Publisher';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Shop Suppliers';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$groupParent = 'Customer Group';
		$groupName = "Test ".$groupParent." Group".$saltGroup;
		$this->createGroup($groupName, $groupParent);

		$this->deleteGroup();

		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}
}
