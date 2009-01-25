<?php
/**
 * @version		$Id: example.php 10094 2008-03-02 04:35:10Z instance $
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

/**
 * Example User Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 		1.5
 */
class plgInstallerExample extends JPlugin {

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgUserExample(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onBeforeExtensionInstall($method, $type, $manifest, $eid) {
		JError::raiseWarning(-1, 'plgInstallerExample::onBeforeExtensionInstall: Installing '. $type .' from '. $method . ($method == 'install' ? ' with manifest supplied' : ' using discovered extension ID '. $eid));
	}

	function onAfterExtensionInstall($installer, $eid) {
		JError::raiseWarning(-1, 'plgInstallerExample::onAfterExtensionInstall: '. ($eid === false ? 'Failed extension install: '. $installer->getError() : 'Extension install successful') . ($eid ? ' with new extension ID '. $eid : ' with no extension ID detected or multiple extension IDs assigned'));
	}

	function onBeforeExtensionUpdate($type, $manifest) {
		JError::raiseWarning(-1, 'plgInstallerExample::onBeforeExtensionUpdate: Updating a '. $type);
	}

	function onAfterExtensionUpdate($installer, $eid) {
		JError::raiseWarning(-1, 'plgInstallerExample::onAfterExtensionUpdate: '. ($eid === false ? 'Failed extension update: '. $installer->getError() : 'Extension update successful') . ($eid ? ' with updated extension ID '. $eid : ' with no extension ID detected or multiple extension IDs assigned'));
	}

	/**
	 * Example store user method
	 *
	 * Method is called before user data is stored in the database
	 *
	 * @param 	array		holds the old user data
	 * @param 	boolean		true if a new user is stored
	 */
	function onBeforeExtensionUninstall($eid)
	{
		JError::raiseWarning(-1, 'plgInstallerExample::onBeforeExtensionUninstall: Uninstalling '. $eid);
	}

	function onAfterExtensionUninstall($installer, $eid, $result) {
		JError::raiseWarning(-1, 'plgInstallerExample::onAfterExtensionUninstall: Uninstallation of '. $eid .' was a '. ($result ? 'success' : 'failure'));
	}
}