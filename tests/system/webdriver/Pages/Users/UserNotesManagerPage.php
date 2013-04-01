<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
class UserNotesManagerPage extends AdminManagerPage
{
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_users&view=notes']";
	protected $url = 'administrator/index.php?option=com_users&view=notes';

	/**
	 *
	 * @var UserNotesManagerPage
	 */
	public $userNotesManagerPage = null;

	public $toolbar = array (

			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Activate' => 'toolbar-publish',
			'Block' => 'toolbar-unpublish',
			'Archive' => 'toolbar-archive',
			'Check In' => 'toolbar-checkin',
			'Trash' => 'toolbar-trash',
			'Empty Trash' => 'toolbar-delete',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
	);

	public $submenu = array (
			'option=com_users&view=users',
			'option=com_users&view=groups',
			'option=com_users&view=levels',
			'option=com_users&view=notes',
			'option=com_categories&extension=com_users'
	);

	public $filters = array (
			'Select Status' => 'filter_published',
			'Select Category' => 'filter_category_id',
	);

	public function addUserNotes($name = 'Test User Notes',  $userName = 'Super User', $otherFields = null)
	{
		$this->clickButton('toolbar-new');
		$editUserNotesPage = $this->test->getPageObject('UserNotesEditPage');
		$editUserNotesPage->setFieldValues(array('Subject' => $name));
		$editUserNotesPage->setUser($userName);
		if (is_array($otherFields))
		{
			$editUserNotesPage->setFieldValues($otherFields);
		}
		$editUserNotesPage->clickButton('toolbar-save');
		$this->test->getPageObject('UserNotesManagerPage');
	}

	public function editUserNotes($name, $fields)
	{
		$this->clickItem($name);

		/* var $editUserNotesPage EditUserNotesPage */
		$editUserNotesPage = $this->test->getPageObject('UserNotesEditPage');
		$editUserNotesPage->setFieldValues($fields);
		$editUserNotesPage->clickButton('toolbar-save');
		$this->test->getPageObject('UserNotesManagerPage');
	}

	public function clickItem($name)
	{
		$this->driver->findElement(By::xPath("//tbody//td[contains(., '" . $name . "')]/../td/a[contains(@href, 'task=note.edit')]"))->click();
	}
}