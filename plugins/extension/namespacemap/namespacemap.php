<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Joomla
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! namespace map updater.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgExtensionNamespacemap extends JPlugin
{
	/**
	 * Handle post extension install update sites
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		// Update / Create new map
		if ($eid)
		{
			JNamespacePsr4Map::create();
		}
	}

	/**
	 * Handle extension uninstall
	 *
	 * @param   JInstaller  $installer  Installer instance
	 * @param   integer     $eid        Extension id
	 * @param   boolean     $removed    Installation result
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUninstall($installer, $eid, $removed)
	{
		// If we have a valid extension ID and the extension was successfully uninstalled wipe out any
		// update sites for it
		if ($eid && $removed)
		{
			// Update / Create new map
			JNamespacePsr4Map::create();
		}
	}

	/**
	 * After update of an extension
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension identifier
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		if ($eid)
		{
			// Update / Create new map
			JNamespacePsr4Map::create();
		}
	}

	/**
	 * After update of an extension
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension identifier
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterSave($installer, $eid)
	{
		if ($eid)
		{
			// Update / Create new map
			JNamespacePsr4Map::create();
		}
	}
}
