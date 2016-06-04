<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Page class for the back-end menu items manager screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class BannerEditPage extends AdminEditPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath =  "//form[@id='banner-form']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = 'administrator/index.php?option=com_banners&view=banner&layout=edit';

	/**
	 * Array of tabs present on this page
	 *
	 * @var    array
	 * @since  3.2
	 */
	public $tabs = array('details', 'otherparams', 'publishing');

	/**
	 * Array of all the field Details of the Edit page, along with the ID and tab value they are present on
	 *
	 * @var array
	 * @since 3.2
	 */
	public $inputFields = array (
			array('label' => 'Name', 'id' => 'jform_name', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Alias', 'id' => 'jform_alias', 'type' => 'input', 'tab' => 'header'),
			array('label' => 'Type', 'id' => 'jform_type', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Image', 'id' => 'jform_params_imageurl', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Width', 'id' => 'jform_params_width', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Height', 'id' => 'jform_params_height', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Alternative Text', 'id' => 'jform_params_alt', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Click URL', 'id' => 'jform_clickurl', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Description', 'id' => 'jform_description', 'type' => 'textarea', 'tab' => 'details'),
			array('label' => 'Status', 'id' => 'jform_state', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Category', 'id' => 'jform_catid', 'type' => 'select', 'tab' => 'details'),			
			array('label' => 'Pinned', 'id' => 'jform_sticky', 'type' => 'fieldset', 'tab' => 'details'),
			array('label' => 'Language', 'id' => 'jform_language', 'type' => 'select', 'tab' => 'details'),
			array('label' => 'Version Note', 'id' => 'jform_version_note', 'type' => 'input', 'tab' => 'details'),
			array('label' => 'Max. Impressions', 'id' => 'jform_imptotal', 'type' => 'input', 'tab' => 'otherparams'),
			array('label' => 'Total Impressions', 'id' => 'jform_impmade', 'type' => 'input', 'tab' => 'otherparams'),
			array('label' => 'Total Clicks', 'id' => 'jform_clicks', 'type' => 'input', 'tab' => 'otherparams'),
			array('label' => 'Client', 'id' => 'jform_cid', 'type' => 'select', 'tab' => 'otherparams'),
			array('label' => 'Purchase Type', 'id' => 'jform_purchase_type', 'type' => 'select', 'tab' => 'otherparams'),
			array('label' => 'Track Impressions', 'id' => 'jform_track_impressions', 'type' => 'select', 'tab' => 'otherparams'),
			array('label' => 'Track Clicks', 'id' => 'jform_track_clicks', 'type' => 'select', 'tab' => 'otherparams'),
			array('label' => 'Start Publishing', 'id' => 'jform_publish_up', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Finish Publishing', 'id' => 'jform_publish_down', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created Date', 'id' => 'jform_created', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created By', 'id' => 'jform_created_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Created by Alias', 'id' => 'jform_created_by_alias', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified Date', 'id' => 'jform_modified', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Modified By', 'id' => 'jform_modified_by', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Revision', 'id' => 'jform_version', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'ID', 'id' => 'jform_id', 'type' => 'input', 'tab' => 'publishing'),
			array('label' => 'Meta Keywords', 'id' => 'jform_metakey', 'type' => 'textarea', 'tab' => 'publishing'),
			array('label' => 'Use Own Prefix', 'id' => 'jform_own_prefix', 'type' => 'fieldset', 'tab' => 'publishing'),
			array('label' => 'Meta Keyword Prefix', 'id' => 'jform_metakey_prefix', 'type' => 'input', 'tab' => 'publishing'),
		);

}

