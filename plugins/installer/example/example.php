<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Example User Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since		1.5
 */
class plgInstallerExample extends JPlugin
{
	function onExtensionBeforeInstall($method, $type, $manifest, $eid)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onExtensionBeforeInstall: Installing '. $type .' from '. $method . ($method == 'install' ? ' with manifest supplied' : ' using discovered extension ID '. $eid));
	}

	function onExtensionAfterInstall($installer, $eid)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onExtensionAfterInstall: '. ($eid === false ? 'Failed extension install: '. $installer->getError() : 'Extension install successful') . ($eid ? ' with new extension ID '. $eid : ' with no extension ID detected or multiple extension IDs assigned'));
	}

	function onExtensionBeforeUpdate($type, $manifest)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onExtensionBeforeUpdate: Updating a '. $type);
	}

	function onExtensionAfterUpdate($installer, $eid)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onExtensionAfterUpdate: '. ($eid === false ? 'Failed extension update: '. $installer->getError() : 'Extension update successful') . ($eid ? ' with updated extension ID '. $eid : ' with no extension ID detected or multiple extension IDs assigned'));
	}

	/**
	 * Example store user method
	 *
	 * Method is called before user data is stored in the database
	 *
	 * @param	array		holds the old user data
	 * @param	boolean		true if a new user is stored
	 */
	function onExtensionBeforeUninstall($eid)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onExtensionBeforeUninstall: Uninstalling '. $eid);
	}

	function onExtensionAfterUninstall($installer, $eid, $result)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onExtensionAfterUninstall: Uninstallation of '. $eid .' was a '. ($result ? 'success' : 'failure'));
	}
}
