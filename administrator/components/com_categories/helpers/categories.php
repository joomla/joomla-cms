<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Categories helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 * @since       1.6
 */
class CategoriesHelper
{

	/**
	 * Configure the Submenu links.
	 *
	 * @param   string  $extension  The extension being used for the categories.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($extension)
	{
		// Avoid nonsense situation.
		if ($extension == 'com_categories')
		{
			return;
		}

		$parts = explode('.', $extension);
		$component = $parts[0];

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			require_once $file;

			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName = $prefix . 'Helper';

			if (class_exists($cName))
			{

				if (is_callable(array($cName, 'addSubmenu')))
				{
					$lang = JFactory::getLanguage();
					// loading language file from the administrator/language directory then
					// loading language file from the administrator/components/*extension*/language directory
					$lang->load($component, JPATH_BASE, null, false, false)
					|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, false)
					|| $lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), $lang->getDefault(), false, false);

					call_user_func(array($cName, 'addSubmenu'), 'categories' . (isset($section) ? '.' . $section : ''));
				}
			}
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   string   $extension   The extension.
	 * @param   integer  $categoryId  The category ID.
	 *
	 * @return  JObject
	 *
	 * @since   1.6
	 */
	public static function getActions($extension, $categoryId = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject;
		$parts = explode('.', $extension);
		$component = $parts[0];

		if (empty($categoryId))
		{
			$assetName = $component;
			$level = 'component';
		}
		else
		{
			$assetName = $component . '.category.' . (int) $categoryId;
			$level = 'category';
		}

		$actions = JAccess::getActions($component, $level);

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	public static function getAssociations($pk, $extension = 'com_content')
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->from('#__categories as c')
			->join('INNER', '#__associations as a ON a.id = c.id AND a.context=' . $db->quote('com_categories.item'))
			->join('INNER', '#__associations as a2 ON a.key = a2.key')
			->join('INNER', '#__categories as c2 ON a2.id = c2.id AND c2.extension = ' . $db->quote($extension))
			->where('c.id =' . (int) $pk)
			->where('c.extension = ' . $db->quote($extension));
		$select = array(
			'c2.language',
			$query->concatenate(array('c2.id', 'c2.alias'), ':') . ' AS id'
		);
		$query->select($select);
		$db->setQuery($query);
		$contentitems = $db->loadObjectList('language');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}

		foreach ($contentitems as $tag => $item)
		{
			// Do not return itself as result
			if ((int) $item->id != $pk)
			{
				$associations[$tag] = $item->id;
			}
		}

		return $associations;
	}
}
