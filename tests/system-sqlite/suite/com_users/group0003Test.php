<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests deleting a User Group that does not exist.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Group0003Test extends SeleniumJoomlaTestCase
{
	function testDeleteGroupMessages()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Browse to User Manager: Groups.\n";
		$this->click("link=Groups");
		$this->waitForPageToLoad("30000");

		echo "Check deletion when no groups are selected.\n";
		$badName='doesNotExist';
		echo "Filter on " . $badName . ".\n";
		$this->type("filter_search", $badName);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		echo "Delete all groups in view.\n";
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a");
		try
		{
			$this->assertEquals("Please first make a selection from the list", $this->getAlert());
		}
		catch (PHPUnit_Framework_AssertionFailedError $e)
		{
			array_push($this->verificationErrors, $this->getTraceFiles($e));
		}

        $this->doAdminLogout();
		$this->deleteAllVisibleCookies();
  }
}

