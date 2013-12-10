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
class LevelManagerPage extends AdminManagerPage
{
	protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_users&view=levels']";
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

	public function addLevel($name='Test Level', $groups = array('Public'))
	{
		$this->clickButton('toolbar-new');
		$editLevelPage = $this->test->getPageObject('LevelEditPage');
		$editLevelPage->setFieldValues(array('Level Title' => $name));
		$editLevelPage->setGroups($groups);
		$editLevelPage->clickButton('toolbar-save');
		$this->levelManagerPage = $this->test->getPageObject('LevelManagerPage');
	}

	public function editLevel($name, $groups)
	{
		$this->clickItem($name);
		$editLevelPage = $this->test->getPageObject('LevelEditPage');
		$editLevelPage->setGroups($groups);
		$editLevelPage->clickButton('toolbar-save');
		$this->levelManagerPage = $this->test->getPageObject('LevelManagerPage');
	}

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