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
  protected $waitForXpath =  "//form[@id='item-form']";
	protected $url = 'administrator/index.php?option=com_tags&view=tag&layout=edit';
	
	public $tabs = array('general', 'publishing', 'metadata');
	
	public $tabLabels = array('Tag Details', 'Publishing Options', 'Metadata Options');

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

