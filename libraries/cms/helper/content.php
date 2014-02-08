<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for standard content style extensions.
 * This class mainly simplifies static helper methods often repeated in individual components
 *
 * @package     Joomla.Libraries
 * @subpackage  Helper
 * @since       3.1
 */
class JHelperContent
{
	/**
	 * Configure the Linkbar. Must be implemented by each extension.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addSubmenu($vName)
	{
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $categoryId  The category ID.
	 * @param   integer  $id          The item ID.
	 * @param   string   $assetName   The asset name
	 *
	 * @return  JObject
	 *
	 * @since   3.1
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function _getActions($categoryId = 0, $id = 0, $assetName = '')
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Reverted a change for version 2.5.6
		$user	= JFactory::getUser();
		$result	= new JObject;

		$path = JPATH_ADMINISTRATOR . '/components/' . $assetName . '/access.xml';

		if (empty($id) && empty($categoryId))
		{
			$section = 'component';
		}
		elseif (empty($id))
		{
			$section = 'category';
			$assetName .= '.category.' . (int) $categoryId;
		}
		else
		{
			// Used only in com_content
			$section = 'article';
			$assetName .= '.article.' . (int) $id;
		}

		$actions = JAccess::getActionsFromFile($path, "/access/section[@name='" . $section . "']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   string   $component  The component name.
	 * @param   string   $section    The access section name.
	 * @param   integer  $id         The item ID.
	 *
	 * @return  JObject
	 *
	 * @since   3.2
	 */
	public static function getActions($component = '', $section = '', $id = 0)
	{
		// Check for deprecated arguments order
		if (is_int($component) || is_null($component))
		{
			$result = self::_getActions($component, $section, $id);

			return $result;
		}

		$user	= JFactory::getUser();
		$result	= new JObject;

		$path = JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml';

		if ($section && $id)
		{
			$assetName = $component . '.' . $section . '.' . (int) $id;
		}
		else
		{
			$assetName = $component;
		}

		$actions = JAccess::getActionsFromFile($path, "/access/section[@name='component']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Gets the current language
	 *
	 * @param   boolean  $detectBrowser  Flag indicating whether to use the browser language as a fallback.
	 *
	 * @return  string  The language string
	 *
	 * @since   3.1
	 * @note    JHelper::getCurrentLanguage is the preferred method
	 */
	public static function getCurrentLanguage($detectBrowser = true)
	{
		$app = JFactory::getApplication();
		$langCode = $app->input->cookie->getString(JApplicationHelper::getHash('language'));

		// No cookie - let's try to detect browser language or use site default
		if (!$langCode)
		{
			if ($detectBrowser)
			{
				$langCode = JLanguageHelper::detectLanguage();
			}
			else
			{
				$langCode = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			}
		}

		return $langCode;
	}

	/**
	 * Gets the associated language ID
	 *
	 * @param   string  $langCode  The language code to look up
	 *
	 * @return  integer  The language ID
	 *
	 * @since   3.1
	 * @note    JHelper::getLanguage() is the preferred method.
	 */
	public static function getLanguageId($langCode)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('lang_id')
			->from('#__languages')
			->where($db->quoteName('lang_code') . ' = ' . $db->quote($langCode));
		$db->setQuery($query);

		$id = $db->loadResult();

		return $id;
	}

	/**
	 * Gets a row of data from a table
	 *
	 * @param   JTable  $table  JTable instance for a row.
	 *
	 * @return  array  Associative array of all columns and values for a row in a table.
	 *
	 * @since   3.1
	 */
	public function getRowData(JTable $table)
	{
		$data = new JHelper;

		return $data->getRowData($table);
	}
}
