<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end Components screen.
 *
 */
class TagManagerPage extends AdminManagerPage
{
  protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_tags']";
	protected $url = 'administrator/index.php?option=com_tags';
	
	public $filters = array(
			'Select State' => 'filter_state',
			'Select Access' => 'filter_access',
			'Select Language' => 'filter_language',
			);
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Publish' => 'toolbar-publish',
			'Unpublish' => 'toolbar-unpublish',
			'Archive' => 'toolbar_archive',
			'Check In' => 'toolbar_check_in',
			'Trash' => 'toolbar_trash',
			'Batch' => 'toolbar_batch',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);
	public function addTag($name='Test Tag')
	{
		$new_name = $name . rand(1,100);
		$login = "testing";
		//echo $new_name; 
		$this->clickButton('toolbar-new');
		$TagEditPage = $this->test->getPageObject('TagEditPage');
		//$TagEditPage->setFieldValue('Title',$new_name);	
		$TagEditPage->setFieldValues(array('Title' => $new_name));
		$TagEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TagManagerPage');
	}
	public function editTag($name, $fields)
	{
		$this->clickItem($name);
		$TagEditPage = $this->test->getPageObject('TagEditPage');
		$TagEditPage->setFieldValues($fields);
		$TagEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TagManagerPage');
		$this->searchFor();
	}
	public function deleteTag($name)
	{
		$this->searchFor($name);
		$this->driver->findElement(By::name("checkall-toggle"))->click();
		$this->driver->findElement(By::xPath(".//div[@id='toolbar-trash']/button"))->click();
		//$this->clickButton('toolbar-trash');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		$this->searchFor();
	}


	
}
