<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end component panel
 *
 */
class TagEditPage extends AdminEditPage
{
  /**
	 * The field type.
	 *
	 * @waitforXpath 	string		Contains the Xpath for the Edit Tag Page Form 
	 */
	protected $waitForXpath =  "//form[@id='item-form']";
	/**
	 * The field type.
	 *
	 * @url 	string		Contains the URL for the Edit Tag Page 
	 */
	protected $url = 'administrator/index.php?option=com_tags&view=tag&layout=edit';
	/**
	 * The field type.
	 *
	 * @tabLabels 	string array		Contains all the labels of the Tabs that are present on the Page 
	 */	
	public $tabs = array('general', 'publishing', 'metadata');
	/**
	 * The field type.
	 *
	 * @tabLabels 	string array		Contains all the labels of the Tabs that are present on the Page 
	 */
	public $tabLabels = array('Tag Details', 'Publishing Options', 'Metadata Options');
	/**
	 * The field type.
	 *
	 * @inputFields 	string array		Contains all the field Details of the Edit page 
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Float', 'id' => 'jform_images_float_fulltext', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Alt', 'id' => 'jform_images_image_fulltext_alt', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Caption', 'id' => 'jform_images_image_fulltext_caption', 'type' => 'input', 'tab' => 'general'),
	);
	
	
}

