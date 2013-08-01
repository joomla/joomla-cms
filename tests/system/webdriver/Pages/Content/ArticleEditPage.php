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
class ArticleEditPage extends AdminEditPage
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
	protected $url = 'administrator/index.php?option=com_content&view=article&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabs = array('general', 'publishing', 'attrib-basic','editor', 'metadata','permissions');

	/**
	 * Array of tab labels for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $tabLabels = array('Article Details','Publishing Options', 'Article Options', 'Configure Edit Screen', 'Metadata Options', 'Article Permissions');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.0
	 */
	public $inputFields = array (
			array('label' => 'Title', 'id' => 'jform_title', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Intro Image', 'id' => 'jform_images_image_intro', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Image Float', 'id' => 'jform_images_float_intro', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Alt text', 'id' => 'jform_images_image_intro_alt', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Caption', 'id' => 'jform_images_image_intro_caption', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Full article image', 'id' => 'jform_images_image_fulltext', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Image Float', 'id' => 'jform_images_float_fulltext', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Alt text', 'id' => 'jform_images_image_fulltext_alt', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Caption', 'id' => 'jform_images_image_fulltext_caption', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Link A', 'id' => 'jform_urls_urla', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Link A Text', 'id' => 'jform_urls_urlatext', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'URL Target Window', 'id' => 'jform_urls_targeta', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Link B', 'id' => 'jform_urls_urlb', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Link B Text', 'id' => 'jform_urls_urlbtext', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'URL Target Window', 'id' => 'jform_urls_targetb', 'type' => 'select', 'tab' => 'general'),
			array('label' => 'Link C', 'id' => 'jform_urls_urlc', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'Link C Text', 'id' => 'jform_urls_urlctext', 'type' => 'input', 'tab' => 'general'),
			array('label' => 'URL Target Window', 'id' => 'jform_urls_targetc', 'type' => 'select', 'tab' => 'general'),

			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by', 'id' => 'jform_created_by_name', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created Date', 'id' => 'jform_created', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'publishing'),

			array('label' => 'Show Title', 'id' => 'jform_attribs_show_title', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Linked Titles', 'id' => 'jform_attribs_link_titles', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Tags', 'id' => 'jform_attribs_show_tags', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Intro Text', 'id' => 'jform_attribs_show_intro', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Position of Article Info', 'id' => 'jform_attribs_info_block_position', 'type' => 'select', 'tab' => 'attrib-basic'),
			array('label' => 'Show Category', 'id' => 'jform_attribs_show_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Link Category', 'id' => 'jform_attribs_link_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Parent', 'id' => 'jform_attribs_show_parent_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Link Parent', 'id' => 'jform_attribs_link_parent_category', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Author', 'id' => 'jform_attribs_show_author', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Link Author', 'id' => 'jform_attribs_link_author', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Create Date', 'id' => 'jform_attribs_show_create_date', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Modify Date', 'id' => 'jform_attribs_show_modify_date', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Publish Date', 'id' => 'jform_attribs_show_publish_date', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Navigation', 'id' => 'jform_attribs_show_item_navigation', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Icons', 'id' => 'jform_attribs_show_icons', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Print Icon', 'id' => 'jform_attribs_show_print_icon', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Email Icon', 'id' => 'jform_attribs_show_email_icon', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Voting', 'id' => 'jform_attribs_show_vote', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Hits', 'id' => 'jform_attribs_show_hits', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Show Unauthorised Links', 'id' => 'jform_attribs_show_noauth', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Positioning of the Links', 'id' => 'jform_attribs_urls_position', 'type' => 'fieldset', 'tab' => 'attrib-basic'),
			array('label' => 'Read More Text', 'id' => 'jform_attribs_alternative_readmore', 'type' => 'input', 'tab' => 'attrib-basic'),
			array('label' => 'Alternative Layout', 'id' => 'jform_attribs_article_layout', 'type' => 'select', 'tab' => 'attrib-basic'),

			array('label' => 'Show Publishing Options', 'id' => 'jform_attribs_show_publishing_options', 'type' => 'fieldset', 'tab' => 'editor'),
			array('label' => 'Show Article Options', 'id' => 'jform_attribs_show_article_options', 'type' => 'fieldset', 'tab' => 'editor'),
			array('label' => 'Administrator Images and Links', 'id' => 'jform_attribs_show_urls_images_backend', 'type' => 'fieldset', 'tab' => 'editor'),
			array('label' => 'Frontend Images and Links', 'id' => 'jform_attribs_show_urls_images_frontend', 'type' => 'fieldset', 'tab' => 'editor'),

			array('label' => 'Meta Description', 'id' => 'jform_metadesc', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'Key Reference', 'id' => 'jform_xreference', 'type' => 'input', 'tab' => 'metadata'),
			array('label' => 'Robots', 'id' => 'jform_metadata_robots', 'type' => 'select', 'tab' => 'metadata'),
			array('label' => 'Author', 'id' => 'jform_metadata_author', 'type' => 'input', 'tab' => 'metadata'),
			array('label' => 'Content Rights', 'id' => 'jform_metadata_rights', 'type' => 'textarea', 'tab' => 'metadata'),
			array('label' => 'External Reference', 'id' => 'jform_metadata_xreference', 'type' => 'input', 'tab' => 'metadata'),

	);


}
