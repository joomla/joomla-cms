<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 * @since  Joomla 3.0
 */
class LevelManagerPage extends AdminManagerPage
{
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_users&view=levels']";

	protected $url = 'administrator/index.php?option=com_users&view=levels';

	/**
	 *
	 * @var LevelManagerPage
	 */
	public $levelManagerPage = null;

	public $toolbar = array (
			'toolbar-new',
			'toolbar-edit',
			'toolbar-delete',
			'toolbar-options',
			'toolbar-help'
			);

	public $submenu = array (
			'option=com_users&view=users',
			'option=com_users&view=groups',
			'option=com_users&view=levels',
			'option=com_users&view=notes',
			'option=com_categories&extension=com_users'
			);

	/**
	 * function to add a level
	 *
	 * @param   string  $name    title of the level
	 * @param   array   $groups  save the array
	 *
	 * @return void
	 */
	public function addLevel($name='Test Level', $groups = array('Public'))
	{
		$this->clickButton('toolbar-new');
		$editLevelPage = $this->test->getPageObject('LevelEditPage');
		$editLevelPage->setFieldValues(array('Level Title' => $name));
		$editLevelPage->setGroups($groups);
		$editLevelPage->clickButton('toolbar-save');
		$this->levelManagerPage = $this->test->getPageObject('LevelManagerPage');
	}

	/**
	 * function to edit a level
	 *
	 * @param   String  $name    title of the level
	 * @param   Array   $groups  stores the value of the group
	 *
	 * @return void
	 */
	public function editLevel($name, $groups)
	{
		$this->clickItem($name);
		$editLevelPage = $this->test->getPageObject('LevelEditPage');
		$editLevelPage->setGroups($groups);
		$editLevelPage->clickButton('toolbar-save');
		$this->levelManagerPage = $this->test->getPageObject('LevelManagerPage');
	}

	/**
	 * function to get the values of the groups
	 *
	 * @param   String  $levelName  title of the level
	 *
	 * @return mixed
	 */
	public function getGroups($levelName)
	{
		$this->clickItem($levelName);
		$levelEditPage = $this->test->getPageObject('LevelEditPage');
		$result = $levelEditPage->getGroups();
		$levelEditPage->clickButton('toolbar-save');
		$this->userManagerPage = $this->test->getPageObject('LevelManagerPage');

		return $result;
	}
}
