<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Redirect: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedirectManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     RedirectManagerPage
	 * @since   3.0
	 */
	protected $redirectManagerPage = null;

	/**
	 * Login to back end and navigate to menu Redirect.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->redirectManagerPage = $cpPage->clickMenu('Redirect', 'RedirectManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * check all input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->redirectManagerPage->clickButton('toolbar-new');
		$redirectEditPage = $this->getPageObject('RedirectEditPage');
		$testElements = $redirectEditPage->getAllInputFields(array('basic'));
		$actualFields = array();

		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}

		$this->assertEquals($redirectEditPage->inputFields, $actualFields);
		$redirectEditPage->clickButton('toolbar-cancel');
		$this->redirectManagerPage = $this->getPageObject('RedirectManagerPage');
	}

	/**
	 * check redirect edit page
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_RedirectEditOpened()
	{
		$this->redirectManagerPage->clickButton('new');
		$redirectEditPage = $this->getPageObject('RedirectEditPage');
		$redirectEditPage->clickButton('cancel');
		$this->redirectManagerPage = $this->getPageObject('RedirectManagerPage');
	}

	/**
	 * check tab IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->redirectManagerPage->clickButton('toolbar-new');
		$redirectEditPage = $this->getPageObject('RedirectEditPage');
		$textArray = $redirectEditPage->getTabIds();
		$this->assertEquals($redirectEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$redirectEditPage->clickButton('toolbar-cancel');
		$this->redirectManagerPage = $this->getPageObject('RedirectManagerPage');
	}

	/**
	 * add Redirect with default values
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addRedirect_WithFieldDefaults_RedirectAdded()
	{
		$salt = rand();
		$src = 'Test' . $salt;
		$this->assertFalse($this->redirectManagerPage->getRowNumber($src), 'Test Link should not be present');
		$this->redirectManagerPage->addRedirect($src);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Link successfully saved') >= 0, 'Redirect Link save should return success');
		$this->assertEquals(1, $this->redirectManagerPage->getRowNumber($src), 'Test link should be in row 1');
		$this->redirectManagerPage->trashAndDelete($src);
		$this->assertFalse($this->redirectManagerPage->getRowNumber($src), 'Test link should not be present');
	}

	/**
	 * add redirect with given fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addRedirect_WithGivenFields_RedirectAdded()
	{
		$salt = rand();
		$src = 'administrator/index.php/dummysrc' . $salt;
		$dest = 'administrator/index.php/dummydest' . $salt;
		$status = 'Enabled';
		/* other than the Default Value */
		$comments = 'Comments are for Sample';

		$this->assertFalse($this->redirectManagerPage->getRowNumber($src), 'Test link should not be present');
		$this->redirectManagerPage->addRedirect($src, $dest, $status, $comments);
		$message = $this->redirectManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Link successfully saved') >= 0, 'Link save should return success');
		$this->assertEquals(1, $this->redirectManagerPage->getRowNumber($src), 'Test Link should be in row 1');
		$values = $this->redirectManagerPage->getFieldValues('RedirectEditPage', $src, array('Source URL', 'Destination URL'));
		$this->assertEquals(array($src,$dest), $values, 'Actual source and Destination should match expected');
		$this->redirectManagerPage->trashAndDelete($src);
		$this->assertFalse($this->redirectManagerPage->getRowNumber($src), 'Test Link should not be present');
	}

	/**
	 * edit redirect and change the value of the input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editRedirect_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$src = 'administrator/index.php/dummysrc' . $salt;
		$dest = 'administrator/index.php/dummydest' . $salt;
		$status = 'Enabled';
		/* Other than the Default Value */
		$comments = 'Comments are for Sample';
		$this->assertFalse($this->redirectManagerPage->getRowNumber($src), 'Test link should not be present');
		$this->redirectManagerPage->addRedirect($src, $dest, $status, $comments);
		$this->redirectManagerPage->editRedirect($src, array('Destination URL' => 'administrator/index.php/dummydest2', 'Comment' => 'New_Sample_Comment'));
		$values = $this->redirectManagerPage->getFieldValues('RedirectEditPage', $src, array('Destination URL', 'Comment'));
		$this->assertEquals(array('administrator/index.php/dummydest2', 'New_Sample_Comment'), $values, 'Actual values should match expected');
		$this->redirectManagerPage->trashAndDelete($src);
	}

	/**
	 * change state of redirect
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeRedirectState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->redirectManagerPage->addRedirect('administrator/index.php/dummysrc');
		$state = $this->redirectManagerPage->getState('administrator/index.php/dummysrc');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->redirectManagerPage->changeRedirectState('administrator/index.php/dummysrc', 'unpublished');
		$state = $this->redirectManagerPage->getState('administrator/index.php/dummysrc');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->redirectManagerPage->trashAndDelete('administrator/index.php/dummysrc');
	}
}
