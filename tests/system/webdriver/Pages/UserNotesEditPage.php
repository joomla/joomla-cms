<?php

require_once 'AdminEditPage.php';

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
class UserNotesEditPage extends AdminEditPage
{
	protected $waitForXpath = "//form[@id='note-form']";
	protected $url = 'administrator/index.php?option=com_users&view=note&layout=edit';

	/**
	 * Array of expected id values for toolbar div elements
	 * @var array
	 */
	public $toolbar = array (
			'Save' => 'toolbar-apply',
			'Save & Close' => 'toolbar-save',
			'Save & New' => 'toolbar-save-new',
			'Cancel' => 'toolbar-cancel',
			'Help' => 'toolbar-help',
	);

	/**
	 * Associative array of expected input fields for the Account Details and Basic Settings tabs
	 * Assigned User UserNotess tab is omitted because that depends on the groups set up in the sample data
	 * @var unknown_type
	 */
	public $inputFields = array (
			array('label' => 'Subject', 'id' => 'jform_subject', 'type' => 'input', 'tab' => 'none'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'none'),
			array('label' => 'Status', 'id' => 'jform_state', 'type' => 'select', 'tab' => 'none'),
			array('label' => 'Review time', 'id' => 'jform_review_time', 'type' => 'input', 'tab' => 'none'),
			array('label' => 'Note', 'id' => 'jform_body', 'type' => 'textarea', 'tab' => 'none'),
	);

	public function getAllInputFields($tabIds = array())
	{
		$return = array();
		$labels = $this->driver->findElements(By::xPath("//fieldset/div[@class='control-group']/div/label"));
		$tabId = 'none';
		foreach ($labels as $label)
		{
			if (($inputField = $this->getInputField($tabId, $label)) !== false)
			{
				$return[] = $inputField;
			}
		}
		return $return;
	}
}