<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Components helper for com_config
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.0
 */
class ConfigHelperComponent
{
	public static function getAllComponents()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('name');
		$query->from('#__extensions');
		$query->where('type=' . $db->quote('component'));
		$query->where('enabled= 1');
		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}

	public static function hasComponentConfig($component)
	{
		return is_file(JPATH_ADMINISTRATOR . '/components/' . $component . '/config.xml');
	}

	public static function getComponentsWithConfig($authCheck = true)
	{
		$result = array();
		$components = self::getAllComponents();
		$user = JFactory::getUser();

		// Remove com_config from the array as that may have weird side effects
		$components = array_diff($components, array("com_config"));

		foreach ($components as $component)
		{
			if (self::hasComponentConfig($component) && (!$authCheck || $user->authorise('core.manage', $component)))
			{
				$result[] = $component;
			}
		}

		return $result;
	}

	public static function loadLanguageForComponents($components)
	{
		$lang = JFactory::getLanguage();

		foreach ($components as $component)
		{
			if (!empty($component)) {
					// Load the core file then
					// Load extension-local file.
					$lang->load($component . '.sys', JPATH_BASE, null, false, false)
				||	$lang->load($component . '.sys', JPATH_ADMINISTRATOR.'/components/' . $component, null, false, false)
				||	$lang->load($component . '.sys', JPATH_BASE, $lang->getDefault(), false, false)
				||	$lang->load($component . '.sys', JPATH_ADMINISTRATOR.'/components/' . $component, $lang->getDefault(), false, false);
			}
		}
	}

}
