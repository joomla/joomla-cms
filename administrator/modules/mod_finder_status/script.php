<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_finder_status
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 */
class Mod_Finder_StatusInstallerScript {

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param	string	$type	The action being performed
	 * @param	string	$parent	The function calling this method
	 *
	 * @return	void
	 * @since	1.8
	 */
	function preflight($type, $parent) {
		// Check if Finder is installed
		if (!JFolder::exists(JPATH_BASE.'/components/com_finder')) {
			JError::raiseNotice(null, JText::_('MOD_FINDER_STATUS_ERROR_COMPONENT'));
			return false;
		}
	}

	/**
	 * Function to perform changes when plugin is initially installed
	 *
	 * @param	$parent
	 *
	 * @return	void
	 * @since	1.6
	 */
	function install($parent) {
		$this->activateModule();
	}

	/**
	 * Function to preconfigure the status module at installation
	 *
	 * @return	void
	 * @since	1.7
	 */
	function activateModule() {
		$db = JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Set the module configuration
		$query->update($db->quoteName('#__modules'));
		$query->set($db->quoteName('published').' = 1');
		$query->set($db->quoteName('title').' = '.$db->quote(JText::_('MOD_FINDER_STATUS_TITLE')));
		$query->set($db->quoteName('position').' = '.$db->quote('status'));
		$query->set($db->quoteName('access').' = 3');
		$query->set($db->quoteName('showtitle').' = 0');
		$query->set($db->quoteName('ordering').' = 2');
		$query->where($db->quoteName('module').' = '.$db->quote('mod_finder_status'));
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseNotice(1, JText::_('MOD_FINDER_STATUS_ERROR_PRECONFIGURE'));
		}

		// Get the module ID
		$query->clear();
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__modules'));
		$query->where($db->quoteName('module').' = '.$db->quote('mod_finder_status'));
		$db->setQuery($query);
		if (!$db->loadObject()) {
			JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
		} else {
			$record = $db->loadObject();
		}
		$moduleId	= json_decode($record->id);

		// Publish the module
		$query->clear();
		$query->insert($db->quoteName('#__modules_menu'));
		$query->values($moduleId.', 0');
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseNotice(1, JText::_('MOD_FINDER_STATUS_ERROR_PUBLISH'));
		}
	}
}
