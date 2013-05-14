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
 * This class tests the  Tags: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class TagManager0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     TagManagerPage
	 * @since   3.0
	 */
	protected $tagManagerPage = null;
	
	/**
	 * Login to back end and navigate to menu Tags.
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->tagManagerPage = $cpPage->clickMenu('Tags', 'TagManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.0
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
		$this->tagManagerPage->clickButton('toolbar-new');
		$tagEditPage = $this->getPageObject('TagEditPage');
		$testElements = $tagEditPage->getAllInputFields(array('general','publishing','metadata'));
		$actualFields = array();
		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}
		$this->assertEquals($tagEditPage->inputFields, $actualFields);
		$tagEditPage->clickButton('toolbar-cancel');
		$this->tagManagerPage = $this->getPageObject('TagManagerPage');
	}
	
	/**
	 * @test
	 */
	public function constructor_OpenEditScreen_TagEditOpened()
	{
		$this->tagManagerPage->clickButton('new');
		$tagEditPage = $this->getPageObject('TagEditPage');
		$tagEditPage->clickButton('cancel');
		$this->tagManagerPage = $this->getPageObject('TagManagerPage');
	}
	
	/**
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->tagManagerPage->clickButton('toolbar-new');
		$tagEditPage = $this->getPageObject('TagEditPage');
		$textArray = $tagEditPage->getTabIds();
		$this->assertEquals($tagEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$tagEditPage->clickButton('toolbar-cancel');
		$this->tagManagerPage = $this->getPageObject('TagManagerPage');
	}
	
	/**
	 * @test
	 */
	public function addTag_WithFieldDefaults_TagAdded()
	{
		$salt = rand();
		$tagName = 'Tag' . $salt;
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test Tag should not be present');
		$this->tagManagerPage->addTag($tagName);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tag successfully saved') >= 0, 'Tag save should return success');
		$this->assertEquals(1, $this->tagManagerPage->getRowNumber($tagName), 'Test Tag should be in row 2');
		$this->tagManagerPage->deleteItem($tagName);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test Tag should not be present');
	}
	
	/**
	 * @test
	 */
	public function addTag_WithGivenFields_TagAdded()
	{
		$salt = rand();
		$tagName = 'Tag' . $salt;
		$caption = 'Sample'. $salt;
		$alt = 'alt' . $salt;
		$float = 'Right'; //Other than the Default Value

		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test tag should not be present');
		$this->tagManagerPage->addTag($tagName,$caption,$alt,$float);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tags successfully saved') >= 0, 'Tag save should return success');
		$this->assertEquals(1, $this->tagManagerPage->getRowNumber($tagName), 'Test test tag should be in row 1');
		$values = $this->tagManagerPage->getFieldValues('TagEditPage', $tagName, array('Title', 'Caption'));
		$this->assertEquals(array($tagName,$caption), $values, 'Actual name, caption should match expected');
		$this->tagManagerPage->deleteItem($tagName);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test tag should not be present');
	}

	/**
	 * @test
	 */
	public function editTag_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		$tagName = 'Tag' . $salt;
		$Caption = 'Caption' . $salt;
		$alt = 'alt' . $salt;
		$float = 'Right';
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test tag should not be present');
		$this->tagManagerPage->addTag($tagName, $Caption, $alt, $float);
		$this->tagManagerPage->editTag($tagName, array('Caption' => 'new_sample_Caption', 'Alt' => 'Sample_Alt'));
		$values = $this->tagManagerPage->getFieldValues('tagEditPage', $tagName, array('Caption', 'Alt'));
		$this->assertEquals(array('new_sample_Caption', 'Sample_Alt'), $values, 'Actual values should match expected');
		$this->tagManagerPage->deleteItem($tagName);
	}
	
	/**
	 * @test
	 */
	public function changeTagState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->tagManagerPage->addTag('Test Tag');
		$state = $this->tagManagerPage->getState('Test Tag');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->tagManagerPage->changeTagState('Test Tag', 'unpublished');
		$state = $this->tagManagerPage->getState('Test Tag');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->tagManagerPage->deleteItem('Test Tag');
	}	
}
