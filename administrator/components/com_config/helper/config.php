<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Components helper for com_config
 *
 * @since  3.0
 */
class ConfigHelperConfig extends JHelperContent
{
	/**
	 * Get an array of all enabled components.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getAllComponents()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('element')
			->from('#__extensions')
			->where('type = ' . $db->quote('component'))
			->where('enabled = 1');
		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}

	/**
	 * Returns true if the component has configuration options.
	 *
	 * @param   string  $component  Component name
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public static function hasComponentConfig($component)
	{
		return is_file(JPATH_ADMINISTRATOR . '/components/' . $component . '/config.xml');
	}

	/**
	 * Returns an array of all components with configuration options.
	 * Optionally return only those components for which the current user has 'core.manage' rights.
	 *
	 * @param   boolean  $authCheck  True to restrict to components where current user has 'core.manage' rights.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getComponentsWithConfig($authCheck = true)
	{
		$result = array();
		$components = self::getAllComponents();
		$user = JFactory::getUser();

		// Remove com_config from the array as that may have weird side effects
		$components = array_diff($components, array('com_config'));

		foreach ($components as $component)
		{
			if (self::hasComponentConfig($component) && (!$authCheck || $user->authorise('core.manage', $component)))
			{
				self::loadLanguageForComponent($component);
				$result[$component] = JApplicationHelper::stringURLSafe(JText::_($component)) . '_' . $component;
			}
		}

		asort($result);

		return array_keys($result);
	}

	/**
	 * Load the sys language for the given component.
	 *
	 * @param   array  $components  Array of component names.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadLanguageForComponents($components)
	{
		foreach ($components as $component)
		{
			self::loadLanguageForComponent($component);
		}
	}

	/**
	 * Load the sys language for the given component.
	 *
	 * @param   string  $component  component name.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public static function loadLanguageForComponent($component)
	{
		if (empty($component))
		{
			return;
		}

		$lang = JFactory::getLanguage();

		// Load the core file then
		// Load extension-local file.
		$lang->load($component . '.sys', JPATH_BASE, null, false, true)
		|| $lang->load($component . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);
	}
}
