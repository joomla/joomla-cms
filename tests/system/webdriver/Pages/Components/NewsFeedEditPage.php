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
 * Page class for the back-end menu Newsfeed manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class NewsFeedEditPage extends AdminEditPage
{
  /**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//form[@id='newsfeed-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('details', 'publishing', 'params-jbasic', 'metadata-jmetadata');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('New', 'Publishing Options', 'Display Options', 'Metadata Options');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_name', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Link', 'id' => 'jform_link', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'First image', 'id' => 'jform_images_image_first', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Image Float', 'id' => 'jform_images_float_first', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Alt text', 'id' => 'jform_images_image_first_alt', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Caption', 'id' => 'jform_images_image_first_caption', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Second image', 'id' => 'jform_images_image_second', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Image Float', 'id' => 'jform_images_float_second', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Alt text', 'id' => 'jform_images_image_second_alt', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Caption', 'id' => 'jform_images_image_second_caption', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by', 'id' => 'jform_created_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Author\'s Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created Date', 'id' => 'jform_created', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Number of Articles', 'id' => 'jform_numarticles', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Cache Time', 'id' => 'jform_cache_time', 'type'=>'input', 'tab'=>'publishing'),
			array('label' => 'Language Direction', 'id' => 'jform_rtl', 'type'=>'select', 'tab'=>'publishing'),
			array('label' => 'Feed Image', 'id' => 'jform_params_show_feed_image', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Feed Description', 'id' => 'jform_params_show_feed_description', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Feed Content', 'id' => 'jform_params_show_item_description', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Characters Count', 'id' => 'jform_params_feed_character_count', 'type'=>'input', 'tab'=>'params-jbasic'),
			array('label' => 'Alternative Layout', 'id' => 'jform_params_newsfeed_layout', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Feed display order', 'id' => 'jform_params_feed_display_order', 'type'=>'select', 'tab'=>'params-jbasic'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type'=>'textarea', 'tab'=>'metadata-jmetadata'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type'=>'textarea', 'tab'=>'metadata-jmetadata'),
			array('label' => 'External Reference', 'id' => 'jform_xreference', 'type'=>'input', 'tab'=>'metadata-jmetadata'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type'=>'select', 'tab'=>'metadata-jmetadata'),
			array('label' => 'Content Rights', 'id' => 'jform_metadata_rights', 'type'=>'input', 'tab'=>'metadata-jmetadata'),
	);


}
