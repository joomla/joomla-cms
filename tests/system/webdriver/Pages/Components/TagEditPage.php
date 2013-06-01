<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end menu items manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class TagEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//form[@id='item-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_tags&view=tag&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('general', 'publishing', 'metadata');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('Tag Details', 'Publishing Options', 'Metadata Options');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Teaser image', 'id' => 'jform_images_image_intro', 'type' => 'input', 'tab' => 'general'),
            array('label' => 'Float', 'id' => 'jform_images_float_intro', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Alt', 'id' => 'jform_images_image_intro_alt', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Caption', 'id' => 'jform_images_image_intro_caption', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Full image', 'id' => 'jform_images_image_fulltext', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Float', 'id' => 'jform_images_float_fulltext', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Alt', 'id' => 'jform_images_image_fulltext_alt', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Caption', 'id' => 'jform_images_image_fulltext_caption', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Author\'s Alias', 'id' => 'jform_created_by_alias', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Created Date', 'id' => 'jform_created_time', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type'=>'textarea', 'tab'=>'metadata'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type'=>'textarea', 'tab'=>'metadata'),
			array('label' => 'Author', 'id' => 'jform_metadata_author', 'type'=>'input', 'tab'=>'metadata'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type'=>'select', 'tab'=>'metadata'),
	);

}

