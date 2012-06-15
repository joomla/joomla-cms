<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Creates test group.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Acl0002Test extends SeleniumJoomlaTestCase
{
	  function testCreateLevel()
  {
  		$this->setUp();
	  	$this->gotoAdmin();
	  	$this->doAdminLogin();
	  	$salt = mt_rand();
		$groupName = 'My Test Group'.$salt;
		$this->createGroup($groupName, 'Administrator');
		$levelName = 'My Test Level'.$salt;
		$this->createLevel($levelName,$groupName);
		sleep(1);
		$this->deleteLevel();
		$this->deleteGroup('My Test Group');
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
  }
}
