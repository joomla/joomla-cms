<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for standard content style extensions.
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
	 */
	public static function getActions($categoryId = 0, $id = 0, $assetName = '')
	{
		// Reverted a change for version 2.5.6
		$user	= JFactory::getUser();
		$result	= new JObject;

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
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
	 */
	public static function getCurrentLanguage($detectBrowser = true)
	{
		$app = JFactory::getApplication();
		$langCode = $app->input->cookie->getString(JApplication::getHash('language'));

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
	public function getRowData($table)
	{
		$fields = $table->getFields();
		$data = array();

		foreach ($fields as &$field)
		{
			$columnName = $field->Field;
			$value = $table->$columnName;
			$data[$columnName] = $value;
		}

		return $data;
	}
}
