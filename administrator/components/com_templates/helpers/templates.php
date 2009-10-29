<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Templates
 */
class TemplatesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Templates_Submenu_Styles'),
			'index.php?option=com_templates&view=styles',
			$vName == 'styles'
		);
		JSubMenuHelper::addEntry(
			JText::_('Templates_Submenu_Templates'),
			'index.php?option=com_templates&view=templates',
			$vName == 'templates'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, 'com_templates'));
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getClientOptions()
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '0', JText::_('Templates_Option_Site'));
		$options[]	= JHtml::_('select.option', '1', JText::_('Templates_Option_Administrator'));

		return $options;
	}

	/**
	 * Get a list of filter options for the templates with styles.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getTemplateOptions($clientId = 0)
	{
		// Build the filter options.
		$db = JFactory::getDbo();

		$db->setQuery(
			'SELECT DISTINCT(template) AS value, template AS text' .
			' FROM #__template_styles' .
			' WHERE client_id = '.(int) $clientId .
			' ORDER BY template'
		);
		$options = $db->loadObjectList();
		return $options;
	}

	function isTemplateAssigned($id)
	{
		$db = &JFactory::getDbo();
		// check if template is assigned
		$query = 'SELECT COUNT(*)' .
				' FROM #__menu' .
				' WHERE template_style_id = '.$db->Quote($id);
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}

	function isTemplateDefault($id)
	{
		$db = &JFactory::getDbo();
		// check if template is assigned
		$query = 'SELECT home' .
				' FROM #__template_styles' .
				' WHERE id = '.$db->Quote($id);
		$db->setQuery($query);
		return $db->loadResult() == 1 ;
	}

	function isTemplateNameAssigned($template,$client_id)
	{
		$db = &JFactory::getDbo();
		if ($client_id==1)
			return 0;
		// check if template is assigned
		$query = 'SELECT COUNT(*) FROM #__menu'.
				' LEFT JOIN #__template_styles'.
				' ON #__menu.template_style_id=#__template_styles.id'.
				' WHERE #__template_styles.template = '.$db->Quote($template);
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}

	function isTemplateNameDefault($template,$client_id)
	{
		$db = &JFactory::getDbo();

		// check if template is assigned
		$query = 'SELECT COUNT(*) FROM #__template_styles'.
				' WHERE template = '.$db->Quote($template).
				' AND client_id = '.$db->Quote($client_id).
				' AND home = 1';
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}

	function parseXMLTemplateFiles($templateBaseDir)
	{
		// Read the template folder to find templates
		jimport('joomla.filesystem.folder');
		$templateDirs = JFolder::folders($templateBaseDir);

		$rows = array();

		// Check that the directory contains an xml file
		foreach ($templateDirs as $templateDir)
		{
			if (!$data = TemplatesHelper::parseXMLTemplateFile($templateBaseDir, $templateDir)){
				continue;
			} else {
				$rows[$templateDir] = $data;
			}
		}

		return $rows;
	}

	function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		// Check of the xml file exists
		if (!is_file($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml')) {
			return false;
		}

		$xml = JApplicationHelper::parseXMLInstallFile($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml');

		if ($xml['type'] != 'template') {
			return false;
		}

		$data = new StdClass();
		$data->directory = $templateDir;

		foreach($xml as $key => $value) {
			$data->$key = $value;
		}

		$data->checked_out = 0;
		$data->mosname = JString::strtolower(str_replace(' ', '_', $data->name));

		return $data;
	}

	function createMenuList($id)
	{
		$db = &JFactory::getDbo();
		// get selected pages for $menulist
		$query = 'SELECT id AS value FROM #__menu'.
				' WHERE template_style_id = '.$db->Quote($id);
		$db->setQuery($query);
		$lookup = $db->loadObjectList();
		if (empty($lookup)) {
			$lookup = array(JHtml::_('select.option',  '-1'));
		}
		// build the html select list
		$options = JHtml::_('menu.linkoptions');
		$result = JHtml::_(
			'select.genericlist',
			$options,
			'selections[]',
			array(
				'id' => 'selections',
				'list.attr' => 'class="inputbox" size="15" multiple="multiple"',
				'list.select' => $lookup,
			)
		);
		return $result;
	}

	function getTemplateName($id)
	{
		$db = &JFactory::getDbo();
		$query = 'SELECT template FROM #__template_styles'.
				' WHERE id = '.$db->Quote($id).'';
		$db->setQuery($query);
		$db->query();
		if ($db->getNumRows() == 0) {
				JError::raiseWarning(500, JText::_('Template not found'));
				return '';
		}
		return $db->loadResult();
	}
}