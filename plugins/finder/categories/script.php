<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Categories
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Categories
 * @since       2.5
 */
class plgFinderCategoriesInstallerScript
{
	/**
	 * Function to perform changes when plugin is initially installed
	 *
	 * @param   object  $parent  The parent object of the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	function install($parent)
	{
		$this->activateButton();
	}

	/**
	 * Function to activate the button at installation
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	function activateButton()
	{
		$db = JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'));
		$query->set($db->quoteName('enabled').' = 1');
		$query->where($db->quoteName('name').' = '.$db->quote('plg_finder_categories'));
		$db->setQuery($query);
		if (!$db->query())
		{
			JError::raiseNotice(1, JText::_('PLG_FINDER_CATEGORIES_ERROR_ACTIVATING_PLUGIN'));
		}
	}
}
