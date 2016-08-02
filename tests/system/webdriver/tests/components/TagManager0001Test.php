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
	 * @return void
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
		$this->tagManagerPage->clickButton('toolbar-new');
		$tagEditPage = $this->getPageObject('TagEditPage');

		// Option to print actual element array

		/* @var $tagEditPage TagEditPage */
// 	 	$tagEditPage->printFieldArray($tagEditPage->getAllInputFields($tagEditPage->tabs));

		$testElements = $tagEditPage->getAllInputFields($tagEditPage->tabs);
		$actualFields = $this->getActualFieldsFromElements($testElements);
		$this->assertEquals($tagEditPage->inputFields, $actualFields);
		$tagEditPage->clickButton('toolbar-cancel');
		$this->tagManagerPage = $this->getPageObject('TagManagerPage');
	}

	/**
	 * check tag edit page
	 *
	 * @return void
	 *
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
	 * check tab Ids
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->tagManagerPage->clickButton('toolbar-new');
		$tagEditPage = $this->getPageObject('TagEditPage');
		$textArray = $tagEditPage->getTabIds();

		// Keep the following line commented to make it easy to generate values for arrays as fields change.

// 		$tagEditPage->printFieldArray($tagEditPage->getAllInputFields($tagEditPage->tabs));

		$this->assertEquals($tagEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$tagEditPage->clickButton('toolbar-cancel');
		$this->tagManagerPage = $this->getPageObject('TagManagerPage');
	}

	/**
	 * add tag with default fields
	 *
	 * @return void
	 *
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
		$this->assertGreaterThanOrEqual(1, $this->tagManagerPage->getRowNumber($tagName), 'Test test tag should be present');
		$this->tagManagerPage->trashAndDelete($tagName);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test Tag should not be present');
	}

	/**
	 * add tag with given fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addTag_WithGivenFields_TagAdded()
	{
		// Other than the Default Value

		$salt = rand();
		$tagName = 'Tag' . $salt;
		$caption = 'Sample' . $salt;
		$alt = 'alt' . $salt;
		$float = 'Right';

		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test tag should not be present');
		$this->tagManagerPage->addTag($tagName, $caption, $alt, $float);
		$message = $this->tagManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Tags successfully saved') >= 0, 'Tag save should return success');
		$this->assertGreaterThanOrEqual(1, $this->tagManagerPage->getRowNumber($tagName), 'Test test tag should be present');
		$values = $this->tagManagerPage->getFieldValues('TagEditPage', $tagName, array('Title', 'Caption'));
		$this->assertEquals(array($tagName, $caption), $values, 'Actual name, caption should match expected');
		$this->tagManagerPage->trashAndDelete($tagName);
		$this->assertFalse($this->tagManagerPage->getRowNumber($tagName), 'Test tag should not be present');
	}

	/**
	 * edit tag and change the value of the fields
	 *
	 * @return void
	 *
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
		$this->tagManagerPage->trashAndDelete($tagName);
	}

	/**
	 * change the state of the tag
	 *
	 * @return void
	 *
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
		$this->tagManagerPage->trashAndDelete('Test Tag');
	}
}
