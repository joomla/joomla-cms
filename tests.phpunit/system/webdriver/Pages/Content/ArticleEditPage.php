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
class ArticleEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//form[@id='item-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_content&view=article&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('general', 'publishing', 'images', 'permissions', 'attrib-basic', 'editor');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Tags', 'id' => 'jform_tags', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Status', 'id' => 'jform_state', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Featured', 'id' => 'jform_featured', 'type' => 'fieldset', 'tab' => 'general'),
			array('label' => 'Access', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Version Note', 'id' => 'jform_version_note', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'articletext', 'id' => 'jform_articletext', 'type' => 'textarea', 'tab' => 'general'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created Date', 'id' => 'jform_created', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by', 'id' => 'jform_created_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified Date', 'id' => 'jform_modified', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified by', 'id' => 'jform_modified_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Revision', 'id' => 'jform_version', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Hits', 'id' => 'jform_hits', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Key Reference', 'id' => 'jform_xreference', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type' => 'select', 'tab' => 'publishing'),
			array('label' => 'Author', 'id' => 'jform_metadata_author', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Content Rights', 'id' => 'jform_metadata_rights', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'External Reference', 'id' => 'jform_metadata_xreference', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Intro Image', 'id' => 'jform_images_image_intro', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Image Float', 'id' => 'jform_images_float_intro', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Alt text', 'id' => 'jform_images_image_intro_alt', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Caption', 'id' => 'jform_images_image_intro_caption', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Full article image', 'id' => 'jform_images_image_fulltext', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Image Float', 'id' => 'jform_images_float_fulltext', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Alt text', 'id' => 'jform_images_image_fulltext_alt', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Caption', 'id' => 'jform_images_image_fulltext_caption', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Link A', 'id' => 'jform_urls_urla', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Link A Text', 'id' => 'jform_urls_urlatext', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'URL Target Window', 'id' => 'jform_urls_targeta', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Link B', 'id' => 'jform_urls_urlb', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Link B Text', 'id' => 'jform_urls_urlbtext', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'URL Target Window', 'id' => 'jform_urls_targetb', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Link C', 'id' => 'jform_urls_urlc', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'Link C Text', 'id' => 'jform_urls_urlctext', 'type' => 'input', 'tab' => 'images'),
			array('label' => 'URL Target Window', 'id' => 'jform_urls_targetc', 'type' => 'select', 'tab' => 'images'),
			array('label' => 'Show Title', 'id' => 'jform_attribs_show_title', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Linked Titles', 'id' => 'jform_attribs_link_titles', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Tags', 'id' => 'jform_attribs_show_tags', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Intro Text', 'id' => 'jform_attribs_show_intro', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Position of Article Info', 'id' => 'jform_attribs_info_block_position', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Category', 'id' => 'jform_attribs_show_category', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Link Category', 'id' => 'jform_attribs_link_category', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Parent', 'id' => 'jform_attribs_show_parent_category', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Link Parent', 'id' => 'jform_attribs_link_parent_category', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Author', 'id' => 'jform_attribs_show_author', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Link Author', 'id' => 'jform_attribs_link_author', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Create Date', 'id' => 'jform_attribs_show_create_date', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Modify Date', 'id' => 'jform_attribs_show_modify_date', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Publish Date', 'id' => 'jform_attribs_show_publish_date', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Navigation', 'id' => 'jform_attribs_show_item_navigation', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Icons', 'id' => 'jform_attribs_show_icons', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Print Icon', 'id' => 'jform_attribs_show_print_icon', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Email Icon', 'id' => 'jform_attribs_show_email_icon', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Voting', 'id' => 'jform_attribs_show_vote', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Hits', 'id' => 'jform_attribs_show_hits', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Unauthorised Links', 'id' => 'jform_attribs_show_noauth', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Positioning of the Links', 'id' => 'jform_attribs_urls_position', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Read More Text', 'id' => 'jform_attribs_alternative_readmore', 'type' => 'input', 'tab' => 'attrib-basic'),
			array('label' => 'Alternative Layout', 'id' => 'jform_attribs_article_layout', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Publishing Options', 'id' => 'jform_attribs_show_publishing_options', 'type' => 'select', 'tab' => 'editor'),
			array('label' => 'Show Article Options', 'id' => 'jform_attribs_show_article_options', 'type' => 'select', 'tab' => 'editor'),
			array('label' => 'Administrator Images and Links', 'id' => 'jform_attribs_show_urls_images_backend', 'type' => 'select', 'tab' => 'editor'),
			array('label' => 'Frontend Images and Links', 'id' => 'jform_attribs_show_urls_images_frontend', 'type' => 'select', 'tab' => 'editor'),
			);

	/**
	 * function to add test to the article\
	 *
	 * @param   String   $text  text to be writen in the article
	 *
	 * @return void
	 */
	public function addArticleText($text)
	{
		$values = array('id' => 'jform_articletext', 'value' => $text);
		$this->setTextAreaValues($values);
	}

	/**
	 * function to set the field values
	 *
	 * @param   array  $array    array stores the value of the different input fields
	 *
	 * @return void
	 */
	public function setFieldValues($array)
	{
		if (isset($array['text']))
		{
			$this->addArticleText($array['text']);
			unset($array['text']);
		}

		if (count($array) > 0)
		{
			parent::setFieldValues($array);
		}

	}
}
