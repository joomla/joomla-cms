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
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	public $tabs = array('details', 'publishing', 'attrib-basic', 'images');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'Parent', 'id' => 'jform_parent_id', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_published', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Note', 'id' => 'jform_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Version Note', 'id' => 'jform_version_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Created Date', 'id' => 'jform_created_time', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created By', 'id' => 'jform_created_user_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Author\'s Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified Date', 'id' => 'jform_modified_time', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified By', 'id' => 'jform_modified_user_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Hits', 'id' => 'jform_hits', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Author', 'id' => 'jform_metadata_author', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type' => 'select', 'tab' => 'publishing'),
			array('label' => 'Alternative Layout', 'id' => 'jform_params_tag_layout', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'CSS Class for tag link.', 'id' => 'jform_params_tag_link_class', 'type' => 'input', 'tab' => 'attrib-basic'),
			array('label' => 'Teaser Image.', 'id' => 'jform_images_image_intro', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Float', 'id' => 'jform_images_float_intro', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Alt', 'id' => 'jform_images_image_intro_alt', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Caption', 'id' => 'jform_images_image_intro_caption', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Full Image', 'id' => 'jform_images_image_fulltext', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Float', 'id' => 'jform_images_float_fulltext', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Alt', 'id' => 'jform_images_image_fulltext_alt', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Caption', 'id' => 'jform_images_image_fulltext_caption', 'type' => 'input', 'tab' => 'images'),
			);

}

