<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Weblink: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class WeblinkManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     weblinkManagerPage
	 * @since   3.2
	 */
	protected $weblinkManagerPage = null;
	
	/**
	 * Login to back end and navigate to menu Weblinks.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->weblinkManagerPage = $cpPage->clickMenu('Weblinks', 'WeblinkManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}
	
	/**
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->weblinkManagerPage->clickButton('toolbar-new');
		$weblinkEditPage = $this->getPageObject('WeblinkEditPage');
		$testElements = $weblinkEditPage->getAllInputFields(array('details', 'publishing', 'params-jbasic', 'metadata-jmetadata'));
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($weblinkEditPage->inputFields, $actualFields);
		$weblinkEditPage->clickButton('toolbar-cancel');
		$this->weblinkManagerPage = $this->getPageObject('WeblinkManagerPage');
	}
	
	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_WeblinkEditOpened()
	{
		$this->weblinkManagerPage->clickButton('new');
		$weblinkEditPage = $this->getPageObject('WeblinkEditPage');
		$weblinkEditPage->clickButton('cancel');
		$this->weblinkManagerPage = $this->getPageObject('WeblinkManagerPage');
	}
	
	/**
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->weblinkManagerPage->clickButton('toolbar-new');
		$weblinkEditPage = $this->getPageObject('WeblinkEditPage');
		$textArray = $weblinkEditPage->getTabIds();
		$this->assertEquals($weblinkEditPage->tabs, $textArray, 'Weblink labels should match expected values.');
		$weblinkEditPage->clickButton('toolbar-cancel');
		$this->weblinkManagerPage = $this->getPageObject('WeblinkManagerPage');
	}
	
	/**
	 * @test
	 */
	public function addWeblink_WithFieldDefaults_WeblinkAdded()
	{
		$salt = rand();
		$weblinkName = 'Weblink' . $salt;
		$url='www.example.com';
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Test Weblink should not be present');
		$this->weblinkManagerPage->addWeblink($weblinkName, $url, false);
		$message = $this->weblinkManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Weblink successfully saved') >= 0, 'Weblink save should return success');
		$this->assertEquals(10, $this->weblinkManagerPage->getRowNumber($weblinkName), 'Test Weblink should be in row 10');
		$this->weblinkManagerPage->deleteItem($weblinkName);
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Test Weblink should not be present');
	}
	
	/**
	 * @test
	 */
	public function addWeblink_WithGivenFields_WeblinkAdded()
	{
		$salt = rand();
		$weblinkName = 'Weblink' . $salt;
		$url='www.example.com';
		$alt='Alternative Text' . $salt;
		$float='Right';
		$caption='Sample Caption' . $salt;

		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Test weblink should not be present');
		$this->weblinkManagerPage->addWeblink($weblinkName, $url, array('Alt text' => $alt, 'Caption' => $caption, 'Image Float' => $float));
		$message = $this->weblinkManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Weblink successfully saved') >= 0, 'Weblink save should return success');
		$this->assertEquals(10, $this->weblinkManagerPage->getRowNumber($weblinkName), 'Test weblink should be in row 10');
		$values = $this->weblinkManagerPage->getFieldValues('WeblinkEditPage', $weblinkName, array('Title', 'Alt text', 'Caption', 'Image Float'));
		$this->assertEquals(array($weblinkName,$alt,$caption,$float), $values, 'Actual title, alt text, caption and image float should match expected');
		$this->weblinkManagerPage->deleteItem($weblinkName);
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Test weblink should not be present');
	}

	/**
	 * @test
	 */
	public function editWeblink_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$weblinkName = 'Weblink' . $salt;
		$url='www.example.com';
		$this->assertFalse($this->weblinkManagerPage->getRowNumber($weblinkName), 'Test weblink should not be present');
		$this->weblinkManagerPage->addWeblink($weblinkName, $url, false);
		$this->weblinkManagerPage->editWeblink($weblinkName, array('Alt text' => 'Alternative Text' . $salt, 'Caption' => 'Sample Caption' . $salt, 'Image Float' => 'Right'));
		$values = $this->weblinkManagerPage->getFieldValues('WeblinkEditPage', $weblinkName, array('Alt text', 'Caption', 'Image Float'));
		$this->weblinkManagerPage->deleteItem($weblinkName);
	}
	
	/**
	 * @test
	 */
	public function changeWeblinkState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$salt = rand();
		$weblinkName = 'Weblink' . $salt;
		$url='www.example.com';
		$this->weblinkManagerPage->addWeblink($weblinkName, $url, false);
		$state = $this->weblinkManagerPage->getState($weblinkName);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->weblinkManagerPage->changeWeblinkState($weblinkName, 'unpublished');
		$state = $this->weblinkManagerPage->getState($weblinkName);
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->weblinkManagerPage->deleteItem($weblinkName);
	}
}
