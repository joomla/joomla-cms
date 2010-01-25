<?php
/**
 * @version		$Id$
 * @package		Joomla.FunctionalTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Basic test of add, edit, and delete Content Category from back end.
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class ControlPanel0004 extends SeleniumJoomlaTestCase
{

 
  function testCreateRemoveCategory()
  {
  	$this->setUp();
  	$this->doAdminLogin();
    $this->click("link=Control Panel");
    $this->waitForPageToLoad("30000");
    
    print("Navigate to Category Manager." . "\n");
    $this->click("link=Category Manager");
    $this->waitForPageToLoad("30000");
    print("New Category." . "\n");
    $this->click("//li[@id='toolbar-new']/a/span");
    $this->waitForPageToLoad("30000");
    print("Create new Category and save." . "\n");
    $this->type("jform_title", "Functional Test Category");
    $this->select("jform_parent_id", "label=ROOT");
    $this->click("//li[@id='toolbar-save']/a/span");
    $this->waitForPageToLoad("30000");
    print("Check that Category is there." . "\n");
    
    $this->type("filter_search", "Functional Test");
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    
    $this->assertEquals("Functional Test Category", $this->getText("link=Functional Test Category"));
    print("Open for editing and change parent from ROOT to News and save" . "\n");
    $this->click("link=Functional Test Category");
    $this->waitForPageToLoad("30000");
    $this->select("jform_parent_id", "label=- News");
    $this->click("//li[@id='toolbar-save']/a/span");
    $this->waitForPageToLoad("30000");
    print("Check that category is there." . "\n");
    $this->assertEquals("Functional Test Category", $this->getText("link=Functional Test Category"));
    $this->click("link=Functional Test Category");
    $this->waitForPageToLoad("30000");
    $this->click("//li[@id='toolbar-cancel']/a/span");
    $this->waitForPageToLoad("30000");
    print("Send new category to Trash." . "\n");
    $this->click("link=Functional Test Category");
    $this->waitForPageToLoad("30000");
    $this->select("jform_published", "label=Trashed");
    $this->click("//li[@id='toolbar-save']/a/span");
    $this->waitForPageToLoad("30000");
    print("Check that new category is not shown." . "\n");
    $this->assertFalse($this->isTextPresent("Functional Test Category"));
    print("Filter Trashed categories." . "\n");
    $this->select("filter_published", "label=Trash");
    $this->waitForPageToLoad("30000");
    print("Select all trashed categories and delete." . "\n");
    $this->click("toggle");
    $this->click("//li[@id='toolbar-delete']/a/span");
    $this->waitForPageToLoad("30000");
    print("Check that new category is not shown." . "\n");
    $this->assertFalse($this->isTextPresent("Functional Test Category"));
    print("Change fileter to Select State." . "\n");
    $this->select("filter_published", "label=- Select State -");
    $this->waitForPageToLoad("30000");
    print("Check that new category is not shown." . "\n");
    $this->assertFalse($this->isTextPresent("Functional Test Category"));
    $this->doAdminLogout();
    print("Finished control_panel0004Test.php." . "\n");
    
  }
}
?>
