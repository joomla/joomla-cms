<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Example Extension Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Extension.Example
 * @since		1.5
 */
class plgExtensionExample extends JPlugin
{
	/**
	 * Handle post extension install update sites
	 *
	 * @param   JInstaller	Installer object
	 * @param   integer  	Extension Identifier
	 * @since   1.6
	 */
	function onExtensionAfterInstall($installer, $eid)
	{
		JError::raiseWarning(-1, 'plgExtensionExample::onExtensionAfterInstall: '. ($eid === false ? 'Failed extension install: '. $installer->getError() : 'Extension install successful') . ($eid ? ' with new extension ID '. $eid : ' with no extension ID detected or multiple extension IDs assigned'));
	}

	/**
	 * Allow to processing of extension data after it is saved.
	 *
	 * @param	object	The data representing the extension.
	 * @param	boolean	True is this is new data, false if it is existing data.
	 * @since	1.6
	 */
	function onExtensionAfterSave($data, $isNew)
	{
	}

	/**
	 * Handle extension uninstall
	 *
	 * @param	JInstaller	Installer instance
	 * @param	int			extension id
	 * @param	int			installation result
	 * @since	1.6
	 */
	function onExtensionAfterUninstall($installer, $eid, $result)
	{
		JError::raiseWarning(-1, 'plgExtensionExample::onExtensionAfterUninstall: Uninstallation of '. $eid .' was a '. ($result ? 'success' : 'failure'));
	}

	/**
	 * After update of an extension
	 *
	 * @param	JInstaller	Installer object
	 * @param	int			Extension identifier
	 * @since	1.6
	 */
	function onExtensionAfterUpdate($installer, $eid)
	{
		JError::raiseWarning(-1, 'plgExtensionExample::onExtensionAfterUpdate: '. ($eid === false ? 'Failed extension update: '. $installer->getError() : 'Extension update successful') . ($eid ? ' with updated extension ID '. $eid : ' with no extension ID detected or multiple extension IDs assigned'));
	}

	/**
	 * @since	1.6
	 */
	function onExtensionBeforeInstall($method, $type, $manifest, $eid)
	{
		JError::raiseWarning(-1, 'plgExtensionExample::onExtensionBeforeInstall: Installing '. $type .' from '. $method . ($method == 'install' ? ' with manifest supplied' : ' using discovered extension ID '. $eid));
	}

	/**
	 * Allow to processing of extension data before it is saved.
	 *
	 * @param	object	The data representing the extension.
	 * @param	boolean	True is this is new data, false if it is existing data.
	 * @since	1.6
	 */
	function onExtensionBeforeSave($data, $isNew)
	{
	}

	/**
	 * @param	int			extension id
	 * @since	1.6
	 */
	function onExtensionBeforeUninstall($eid)
	{
		JError::raiseWarning(-1, 'plgExtensionExample::onExtensionBeforeUninstall: Uninstalling '. $eid);
	}

	/**
	 * @since	1.6
	 */
	function onExtensionBeforeUpdate($type, $manifest)
	{
		JError::raiseWarning(-1, 'plgExtensionExample::onExtensionBeforeUpdate: Updating a '. $type);
	}
}
