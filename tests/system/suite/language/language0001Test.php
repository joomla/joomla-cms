<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Tests error messages associated with actions performed when nothing is selected.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Language0001Test extends SeleniumJoomlaTestCase
{
	function testCheckNotSelectedErrorMessages()
	{
	$this->setUp();
	$this->gotoAdmin();
	$this->doAdminLogin();
	$filterOn='doesNotExist';

	$this->jClick('User Manager');
	$screen="User Manager: Users";
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Activate';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Block';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-delete']/button");
	$button='Delete';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }

	$this->jClick('Groups');
	$screen="User Manager: User Groups";
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Delete')]");
	$button='Delete';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }

	$this->jClick('Access Levels');
	$screen='Access Levels';
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Delete')]");
	$button='Delete';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
	$this->jClick('Menu Manager');
    $this->click("link=Menu Items");
    $this->waitForPageToLoad("30000");
	$screen='Menu Manager: Menu Items';
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Home')]");
	$button='Default';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->jClick('Article Manager');
	$screen='Article Manager: Articles';
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-archive']/button");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//div/ul[@id='submenu']/li/a[contains(., 'Categories')]");
	$this->waitForPageToLoad("30000");
    $screen='Category Manager';
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-trash']/button");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("link=Featured Articles");
	$screen='Featured Articles';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }

    $this->click("//ul[@id='menu-com-banners']/li[1]/a");
	$screen='Banner Manager: Banners';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//ul[@id='submenu']/li/a[contains(., 'Clients')]");
	$screen='Banner Manager: Clients';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
       $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//ul[@id='submenu']/li/a[contains(., 'Categories')]");
	$screen='Category Manager: Banners';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//ul[@id='menu-com-contact']/li[1]/a");
	$screen='Contact Manager: Contacts';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-archive']/button");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//ul[@id='submenu']/li/a[contains(., 'Categories')]");
	$screen='Category Manager: Contacts';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-archive']/button");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//a[contains(., 'Feeds')]");
	$screen='News Feed Manager: News Feeds';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//a[contains(@href, 'option=com_categories&extension=com_newsfeeds')]");
	$screen='Category Manager: Newsfeeds';
	$this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Archive')]");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("link=Weblinks");
	$screen='Web Links Manager: Web Links';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-archive']/button");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//a[contains(@href, 'option=com_categories&extension=com_weblinks')]");
	$screen='Category Manager: Weblinks';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-archive']/button");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("link=Redirect");
	$screen='Redirect Manager: Links';
	$this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-archive']/button");
	$button='Archive';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("link=Extension Manager");
    $this->waitForPageToLoad("30000");
    $this->click("link=Manage");
	$screen='Extension Manager: Manage';
    $this->waitForPageToLoad("30000");
    $this->filterView($filterOn);
    $this->click("//button[@type='submit']");
    $this->waitForPageToLoad("30000");
    try
	{
        $this->assertTrue($this->isTextPresent("There are no extensions installed matching your query"),$screen .' screen error message not displayed or changed');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
    	$this->assertEquals("Please first make a selection from the list", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//Button[contains(., 'Uninstall')]");
	$button='Uninstall';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
    	$this->assertEquals("Please first make a selection from the list", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
	$this->click("link=Module Manager");
    $screen='Module Manager: Modules';
	$this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-copy']/button");
	$button='Copy';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Check In')]");
	$button='Check In';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//a[contains(., 'Plug-in Manager')]");
	$screen='Plug-in Manager: Plug-ins';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-publish']/button");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-unpublish']/button");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//div[@id='toolbar-checkin']/button");
	$button='Check in';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//a[contains(., 'Template Manager')]");
	$screen='Template Manager: Styles';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("//div[@id='toolbar-default']/button");
	$button='Default';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
    	$this->assertEquals("Please first make a selection from the list", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//div[@id='toolbar-edit']/button");
	$button='Edit';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
    	$this->assertEquals("Please first make a selection from the list", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//Button[contains(., 'Duplicate')]");
	$button='Duplicate';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");

    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert());
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//Button[contains(., 'Delete')]");
	$button='Delete';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
    	$this->assertEquals("Please first make a selection from the list", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
	$this->click("link=Language Manager");
    $this->waitForPageToLoad("30000");
    $this->click("//a[contains(@href, 'option=com_languages&view=languages')]");
	$screen='Language Manager: Content Languages';
    $this->waitForPageToLoad("30000");
	$this->filterView($filterOn);
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Publish')]");
	$button='Publish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Unpublish')]");
	$button='Unpublish';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("checkall-toggle");
    $this->click("//Button[contains(., 'Trash')]");
	$button='Trash';
    $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
    try
	{
        $this->assertEquals("Please first make a selection from the list", $this->getAlert(), 'Should get alert message');
    }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
        array_push($this->verificationErrors, $this->getTraceFiles($e));
    }
    $this->click("//a[contains(., 'Mass Mail Users')]");
	$screen='Mass Mail';
    $this->waitForPageToLoad("30000");
	$this->click("//Button[contains(., 'Send email')]");
	try
	{
	    $this->assertEquals("Please enter a subject", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
	    array_push($this->verificationErrors, $this->getTraceFiles($e));
	}
	$this->type("jform_subject", "test");
	$this->click("//Button[contains(., 'Send email')]");
    try
	{
	     $this->assertEquals("Please enter a message", $this->getAlert());
	}
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
	    array_push($this->verificationErrors, $this->getTraceFiles($e));
	}
	$this->type("jform_message", "test");
    $this->click("//div[@id='toolbar-envelope']/button");
	$button='Send';
   $this->jPrint ("Testing error message when clicking $button button with nothing selected at $screen screen.\n");
   $this->waitForPageToLoad("30000");
   try
	{
       $this->assertTrue($this->isTextPresent("No users could be found in this group."));
   }
	catch (PHPUnit_Framework_AssertionFailedError $e)
	{
       array_push($this->verificationErrors, $this->getTraceFiles($e));
   }
    $this->gotoAdmin();
    $this->doAdminLogout();
	$this->deleteAllVisibleCookies();
  }
}

