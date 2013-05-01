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
        /**
	 * The field type.
	 *
	 * @WaitForXpath 	string 		Contains the Xpath for the Page to be loaded
	 */
        protected $waitForXpath =  "//ul/li/a[@href='index.php?option=com_tags']";
	/**
	 * The field type.
	 *
	 * @url 	string 		Contains the url for the Page to be loaded
	 */
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
	
	/**
	 * Method to  add a Tag in the Components Fields.
	 *
	 * @param   $name  String  The Name of the Tag that we want to add.
	 *
	 * @return  null.
	 *
	 * 
	 */
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
	/**
	 * Method to  edit a existing  Tag in the Components Fields.
	 *
	 * @param   $name  String  The Name of the Tag that we want to edit.
	 *
	 * @param   $fields  String  The value of other fields that we want to change of the Tag.
	 * 
	 * @return  null.
	 *
	 * 
	 */
	
	public function editTag($name, $fields)
	{
		$this->clickItem($name);
		$TagEditPage = $this->test->getPageObject('TagEditPage');
		$TagEditPage->setFieldValues($fields);
		$TagEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('TagManagerPage');
		$this->searchFor();
	}
	/**
	 * Method to  delete a existing Tag in the Components Fields.
	 *
	 * @param   $name  String  The Name of the Tag that we want to delete.
	 * 
	 * @return  null.
	 *
	 * 
	 */
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
