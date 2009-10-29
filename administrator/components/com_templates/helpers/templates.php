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

	function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		$data = new JObject;

		// Check of the xml file exists
		$filePath = JPath::clean($templateBaseDir.'/templates/'.$templateDir.'/templateDetails.xml');
		if (is_file($filePath))
		{
			$xml = JApplicationHelper::parseXMLInstallFile($filePath);

			if ($xml['type'] != 'template') {
				return false;
			}

			foreach ($xml as $key => $value) {
				$data->set($key, $value);
			}
		}

		return $data;
	}
}