<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests creating and deleting a User Group
 */
require_once 'SeleniumJoomlaTestCase.php';

class Group0001Test extends SeleniumJoomlaTestCase
{
	function testCreatDeleteGroup()
	{
  	$this->setUp();
  	$this->doAdminLogin();
    $this->click("link=Groups");
    $this->waitForPageToLoad("30000");  	
	echo "Create new group Article Administrator\n";
    $this->click("link=New");
    $this->waitForPageToLoad("30000");
    $saltGroup = mt_rand();
    $this->type("jform_title", "Test Group".$saltGroup);
    $this->select("jformparent_id", "label=- Registered");
    $this->click("link=Save & Close");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Item successfully saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }  	
    echo "Delete Article Administrator group.\n";	
    $this->type("filter_search", "Test Group".$saltGroup);
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    $this->click("toggle");
    $this->click("link=Delete");
    $this->waitForPageToLoad("30000");
    try {
    	$this->assertTrue($this->isTextPresent("1 item(s) successfully deleted."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
    	array_push($this->verificationErrors, $e->toString());
    }
	$this->doAdminLogout();	        	 
  }
}
?>