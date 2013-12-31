<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests creating and deleting a User Group
 */
require_once 'SeleniumJoomlaTestCase.php';

class Group0001Test extends SeleniumJoomlaTestCase
{
	function testCreatDeleteGroup()
	{
  	$this->setUp();
	$this->gotoAdmin();
  	$this->doAdminLogin();
    $this->click("link=Groups");
    $this->waitForPageToLoad("30000");
	$this->jPrint ("Create new group Article Administrator\n");
    $this->click("//div[@id='toolbar-new']/button");
    $this->waitForPageToLoad("30000");
    $saltGroup = mt_rand();
    $this->type("jform_title", "Test Group".$saltGroup);
    $this->select("jform_parent_id", "label=- Registered");
    $this->click("//div[@id='toolbar-save']/button");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("successfully saved"), 'Save message not shown');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->jPrint ("Delete Article Administrator group.\n");
    $this->type("filter_search", "Test Group".$saltGroup);
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-delete']/button");
    $this->waitForPageToLoad("30000");
    try {
    	$this->assertTrue($this->isTextPresent("success"), 'Deleted message not shown');
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
    	array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
	$this->doAdminLogout();
	$this->countErrors();
	$this->deleteAllVisibleCookies();
  }
}

