<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	 * Load the sys language for the given component.
	 *
	 * @param   array  $components  Array of component names.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadLanguageForComponents($components, $currentComponent = null)
	{
		$lang = JFactory::getLanguage();

		// Load the current component, if it is not null
		if(!empty($currentComponent))
		{
			$lang->load($currentComponent, JPATH_ADMINISTRATOR , null, false, true) ||
			$lang->load($currentComponent, JPATH_ADMINISTRATOR . '/components/' . $currentComponent, null, false, true);
		}

		foreach ($components as $component)
		{
			if (empty($component))
			{
				continue;
			}

			// Load the core file then
			// Load extension-local file.
			$lang->load($component->element . '.sys', JPATH_BASE, null, false, true) ||
			$lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, null, false, true);
		}

	}
}
