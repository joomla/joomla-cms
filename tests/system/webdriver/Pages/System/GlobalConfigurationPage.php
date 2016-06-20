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
 * Class for the back-end control panel screen.
 *
 */
class GlobalConfigurationPage extends AdminEditPage
{
	protected $waitForXpath =  "//a[@href='#page-site']";
	protected $url = 'administrator/index.php?option=com_config';

	public $tabs = array('page-site', 'page-system', 'page-server', 'page-permissions', 'page-filters');
	public $tabLabels = array('Site', 'System', 'Server', 'Permissions', 'Text Filters');

	public $toolbar = array (
			'Save' => 'toolbar-apply',
			'Save & Close' => 'toolbar-save',
			'Cancel' => 'toolbar-cancel',
			'Help' => 'toolbar-help',
	);

	/**
	 * Array of all input fields on the page (except for Permissions and Text Filters tabs). Each value is an associated array as follows:
	 *   label: text of the label -- must be unique!
	 *   id: id attribute of the input element
	 *   type: type attribute of the input element: text, textarea, select, or fieldset (used for radio groups).
	 *
	 * This array is used to set values on the page. You can set a value with just the label text and the desired
	 * value. The id and element type are found here and the correct method is called to set the value.
	 *
	 * @var array
	 */
	public $inputFields = array(
			array('label' => 'Site Name', 'id' => 'jform_sitename', 'type' => 'input', 'tab' => 'page-site'),
			array('label' => 'Site Offline', 'id' => 'jform_offline', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Offline Message', 'id' => 'jform_display_offline_message', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Custom Message', 'id' => 'jform_offline_message', 'type' => 'textarea', 'tab' => 'page-site'),
			array('label' => 'Offline Image', 'id' => 'jform_offline_image', 'type' => 'input', 'tab' => 'page-site'),
			array('label' => 'Mouse-over Edit Icons for', 'id' => 'jform_frontediting', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Default Editor', 'id' => 'jform_editor', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Default Captcha', 'id' => 'jform_captcha', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Default Access Level', 'id' => 'jform_access', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Default List Limit', 'id' => 'jform_list_limit', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Default Feed Limit', 'id' => 'jform_feed_limit', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Feed Email Address', 'id' => 'jform_feed_email', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Site Meta Description', 'id' => 'jform_MetaDesc', 'type' => 'textarea', 'tab' => 'page-site'),
			array('label' => 'Site Meta Keywords', 'id' => 'jform_MetaKeys', 'type' => 'textarea', 'tab' => 'page-site'),
			array('label' => 'Robots', 'id' => 'jform_robots', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Content Rights', 'id' => 'jform_MetaRights', 'type' => 'textarea', 'tab' => 'page-site'),
			array('label' => 'Show Author Meta Tag', 'id' => 'jform_MetaAuthor', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Show Joomla! Version', 'id' => 'jform_MetaVersion', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Search Engine Friendly URLs', 'id' => 'jform_sef', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Use URL Rewriting', 'id' => 'jform_sef_rewrite', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Adds Suffix to URL', 'id' => 'jform_sef_suffix', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Unicode Aliases', 'id' => 'jform_unicodeslugs', 'type' => 'fieldset', 'tab' => 'page-site'),
			array('label' => 'Include Site Name in Page Titles', 'id' => 'jform_sitename_pagetitles', 'type' => 'select', 'tab' => 'page-site'),
			array('label' => 'Cookie Domain', 'id' => 'jform_cookie_domain', 'type' => 'input', 'tab' => 'page-site'),
			array('label' => 'Cookie Path', 'id' => 'jform_cookie_path', 'type' => 'input', 'tab' => 'page-site'),
			array('label' => 'Path to Log Folder', 'id' => 'jform_log_path', 'type' => 'input', 'tab' => 'page-system'),
			array('label' => 'Help Server', 'id' => 'jform_helpurl', 'type' => 'select', 'tab' => 'page-system'),
			array('label' => 'Debug System', 'id' => 'jform_debug', 'type' => 'fieldset', 'tab' => 'page-system'),
			array('label' => 'Debug Language', 'id' => 'jform_debug_lang', 'type' => 'fieldset', 'tab' => 'page-system'),
			array('label' => 'Cache', 'id' => 'jform_caching', 'type' => 'select', 'tab' => 'page-system'),
			array('label' => 'Cache Handler', 'id' => 'jform_cache_handler', 'type' => 'select', 'tab' => 'page-system'),
			array('label' => 'Cache Time', 'id' => 'jform_cachetime', 'type' => 'input', 'tab' => 'page-system'),
			array('label' => 'Session Lifetime', 'id' => 'jform_lifetime', 'type' => 'input', 'tab' => 'page-system'),
			array('label' => 'Session Handler', 'id' => 'jform_session_handler', 'type' => 'select', 'tab' => 'page-system'),
			array('label' => 'Path to Temp Folder', 'id' => 'jform_tmp_path', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'Gzip Page Compression', 'id' => 'jform_gzip', 'type' => 'fieldset', 'tab' => 'page-server'),
			array('label' => 'Error Reporting', 'id' => 'jform_error_reporting', 'type' => 'select', 'tab' => 'page-server'),
			array('label' => 'Force SSL', 'id' => 'jform_force_ssl', 'type' => 'select', 'tab' => 'page-server'),
			array('label' => 'Server Time Zone', 'id' => 'jform_offset', 'type' => 'select', 'tab' => 'page-server'),
			array('label' => 'Enable FTP', 'id' => 'jform_ftp_enable', 'type' => 'fieldset', 'tab' => 'page-server'),
			array('label' => 'Enable Proxy', 'id' => 'jform_proxy_enable', 'type' => 'fieldset', 'tab' => 'page-server'),
			array('label' => 'Database Type', 'id' => 'jform_dbtype', 'type' => 'select', 'tab' => 'page-server'),
			array('label' => 'Host', 'id' => 'jform_host', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'Database Username', 'id' => 'jform_user', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'Database Name', 'id' => 'jform_db', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'Database Tables Prefix', 'id' => 'jform_dbprefix', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'Send Mail', 'id' => 'jform_mailonline', 'type' => 'fieldset', 'tab' => 'page-server'),
			array('label' => 'Mailer', 'id' => 'jform_mailer', 'type' => 'select', 'tab' => 'page-server'),
			array('label' => 'From email', 'id' => 'jform_mailfrom', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'From Name', 'id' => 'jform_fromname', 'type' => 'input', 'tab' => 'page-server'),
			array('label' => 'Disable Mass Mail', 'id' => 'jform_massmailoff', 'type' => 'fieldset', 'tab' => 'page-server')
			);

	public $permissions = array('core.login.site', 'core.login.admin', 'core.login.offline', 'core.admin', 'core.manage', 'core.create', 'core.delete', 'core.edit', 'core.edit.state', 'core.edit.own');






	public function getPermissionInputFields($groupId)
	{
		$this->selectTab('page-permissions');
		$this->driver->findElement(By::xPath("//a[@href='#permission-" . $groupId . "']"))->click();
		foreach ($this->permissions as $permission)
		{
			$id = 'jform_rules_' . $permission . '_' . $groupId;
			$label = $this->driver->findElement(By::xPath("//label[@for='" . $id . "']"));
			$text = $label->getText();
			$input = $this->driver->findElement(By::id($id));
			$this->driver->executeScript($this->moveToElementByAttribute, array('for', $id));
			sleep(1);
			$tip = $label->findElement(By::xPath("//label[@class='tip'][@for='" . $id . "']"));
			$tipText = $tip->getAttribute('title');
			$object = new stdClass();
			$object->tab = $this->driver->findElement(By::xPath("//a[@href='#page-permissions']"))->getText();
			$object->labelText = $label->getText();
			$object->tipText = $tipText;
			$object->tag = $input->getTagName();
			$object->id = $id;
			$object->type = $input->getAttribute('type');

			$object->element = $input;
			$result[] = $object;
		}
		return $result;
	}

	public function getTabIds()
	{
		$tabs = $this->driver->findElements(By::xPath("//div[@id='config-document']/div"));
		$return = array();
		foreach ($tabs as $tab)
		{
			$return[] = $tab->getAttribute('id');
		}
		return $return;
	}

	/**
	 * Output help screen for the page.
	 */
	public function toWikiHelp()
	{
		$inputFields = $this->getAllInputFields($this->getTabIds());
		$tabs = $this->tabs;
		$helpText = array();
		foreach ($inputFields as $el)
		{
			$this->selectTab($el->tab);
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			if ($el->tag == 'fieldset')
			{
				$helpText[$el->tab][] = $this->toWikiHelpRadio($el);
			}
			elseif ($el->tag == 'select')
			{
				$helpText[$el->tab][] = $this->toWikiHelpSelect($el);
			}
			else
			{
				$helpText[$el->tab][] = "*'''" . $el->labelText . ":''' " . $this->getToolTip($el->tab, $el->id . '-lbl') . "\n";
			}
		}

		// Get permissions help just for the public group
		$permissionsText = $this->driver->findElement(By::xPath("//a[@href='#page-permissions']"))->getText();
		$helpText[$permissionsText] = $this->toWikiHelpPermissions('1');
//		$filtersText = $this->driver->findElement(By::id('filters'))->getText();
//		$helpText[$filtersText] = $this->toWikiHelpFilters('1');
		foreach ($tabs as $tab)
		{
			$tabText = $this->driver->findElement(By::xPath("//a[@href='#" . $tab . "']"))->getText();
			$result[] = '===' . $tabText . "===\n";
			if (isset($helpText[$tabText]))
			{
				$result = array_merge($result, $helpText[$tabText]);
			}
		}
		return implode("", $result);

	}



	/**
	 * Prepare wiki text for permissions tab
	 *
	 */
	public function toWikiHelpPermissions($groupId)
	{
		$objects = $this->getPermissionInputFields($groupId);
		foreach ($objects as $object)
		{
			$listElement = str_replace('.', '_', $object->id);
			$optionContainer = $this->driver->findElement(By::xPath("//div[@id='" . $listElement . "_chzn']"));
			$optionContainer->findElement(By::tagName('a'))->click();
			$optionList = $optionContainer->findElement(By::tagName('ul'));
			$optionText = $this->getOptionText($optionList);
			$toolTip = $object->element->getAttribute('title') . ". " . $object->tipText;
			$helpText[] = "*'''" . $object->labelText . ":''' (" . implode('/', $optionText) . "). " . $toolTip . "\n";
			$optionContainer->findElement(By::tagName('a'))->click();
		}
		return $helpText;
	}

	/**
	 * Prepare wiki text for filters tab
	 *
	 */
	public function toWikiHelpFilters($groupId)
	{
		$el = $this->driver->findElement(By::xPath("//a[contains(@href, '#page-filters')]"));
		$el->click();
		$tabText = $el->getText();
		$heading = $this->driver->findElement(By::xPath("//div[@id='page-filters']//legend"))->getText();
		$subText = $this->driver->findElement(By::xPath("//div[@id='page-filters']//p"))->getText();
		$id = "jform_filters" . $groupId . "_filter_type";
		$toolTip = $this->getToolTip($tabText, $id);

		$subHeading = $this->driver->findElement(By::xPath("//table[@id='filter-config']//th[2]"))->getText();
		$filterOptions = $this->getOptionText($this->driver->findElement(By::id($id)));
		sleep(1);
		$helpText[] = "====" . $heading . "====\n";
		$helpText[] = $subText . "\n";
		$helpText[] = "*'''" . $subHeading . ":''' (" . implode("/", $filterOptions) . ")\n";
		$helpText[] = $toolTip;

		return $helpText;
	}

	/**
	 * Change Editor Mode from the Configuration Page
	 *
	 * @param string   $mode	   Editor Mode that the user wants to set
	 *
	 */
	public function changeEditorMode($mode='No Editor')
	{

		switch (strtoupper($mode))
		{
			case 'NO EDITOR':
			case 'NONE':
				$select = 'Editor - None';
				break;

			case 'CODEMIRROR':
				$select = 'Editor - CodeMirror';

			case 'TINYMCE':
			case 'TINY':
			default:
				$select = 'Editor - TinyMCE';
				break;
		}
		$this->setFieldValues(array('Default Editor'=>$select));
		$this->clickButton('Save & Close');
	}
}
