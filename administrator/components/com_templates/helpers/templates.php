<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Templates component helper.
 *
 * @since  1.6
 */
class TemplatesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_TEMPLATES_SUBMENU_STYLES'),
			'index.php?option=com_templates&view=styles',
			$vName == 'styles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_TEMPLATES_SUBMENU_TEMPLATES'),
			'index.php?option=com_templates&view=templates',
			$vName == 'templates'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Get list of actions
		$result = JHelperContent::getActions('com_templates');

		return $result;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 */
	public static function getClientOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JSITE'));
		$options[] = JHtml::_('select.option', '1', JText::_('JADMINISTRATOR'));

		return $options;
	}

	/**
	 * Get a list of filter options for the templates with styles.
	 *
	 * @param   mixed  $clientId  The CMS client id (0:site | 1:administrator) or '*' for all.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 */
	public static function getTemplateOptions($clientId = '*')
	{
		// Build the filter options.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('element', 'value'))
			->select($db->quoteName('name', 'text'))
			->select($db->quoteName('extension_id', 'e_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('template'))
			->where($db->quoteName('enabled') . ' = 1')
			->order($db->quoteName('client_id') . ' ASC')
			->order($db->quoteName('name') . ' ASC');

		if ($clientId != '*')
		{
			$query->where($db->quoteName('client_id') . ' = ' . (int) $clientId);
		}

		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}

	/**
	 * TODO
	 *
	 * @param   string  $templateBaseDir  TODO
	 * @param   string  $templateDir      TODO
	 *
	 * @return  boolean|JObject
	 */
	public static function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		$data = new JObject;

		// Check of the xml file exists
		$filePath = JPath::clean($templateBaseDir . '/templates/' . $templateDir . '/templateDetails.xml');

		if (is_file($filePath))
		{
			$xml = JInstaller::parseXMLInstallFile($filePath);

			if ($xml['type'] != 'template')
			{
				return false;
			}

			foreach ($xml as $key => $value)
			{
				$data->set($key, $value);
			}
		}

		return $data;
	}

	/**
	 * TODO
	 *
	 * @param   integer  $clientId     TODO
	 * @param   string   $templateDir  TODO
	 *
	 * @return  boolean|array
	 *
	 * @since   3.0
	 */
	public static function getPositions($clientId, $templateDir)
	{
		$positions = array();

		$templateBaseDir = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
		$filePath = JPath::clean($templateBaseDir . '/templates/' . $templateDir . '/templateDetails.xml');

		if (is_file($filePath))
		{
			// Read the file to see if it's a valid component XML file
			$xml = simplexml_load_file($filePath);

			if (!$xml)
			{
				return false;
			}

			// Check for a valid XML root tag.

			// Extensions use 'extension' as the root tag.  Languages use 'metafile' instead

			if ($xml->getName() != 'extension' && $xml->getName() != 'metafile')
			{
				unset($xml);

				return false;
			}

			$positions = (array) $xml->positions;

			if (isset($positions['position']))
			{
				$positions = (array) $positions['position'];
			}
			else
			{
				$positions = array();
			}
		}

		return $positions;
	}
}
