<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Joomla
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Installer\Installer as JInstaller;

/**
 * Joomla! namespace map updater.
 *
 * @since  4.0.0
 */
class PlgExtensionNamespacemap extends JPlugin
{
	/**
	 * The namespace map file creator
	 *
	 * @var JNamespacePsr4Map
	 */
	private $fileCreator = null;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                $config    An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                         (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->fileCreator = new JNamespacePsr4Map;

		parent::__construct($subject, $config);
	}

	/**
	 * Handle post extension install update sites
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		// Update / Create new map
		if ($eid)
		{
			$this->fileCreator->create();
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
	 * @since   4.0.0
	 */
	public function onExtensionAfterUninstall($installer, $eid, $removed)
	{
		// If we have a valid extension ID and the extension was successfully uninstalled wipe out any
		// update sites for it
		if ($eid && $removed)
		{
			// Update / Create new map
			$this->fileCreator->create();
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
	 * @since   4.0.0
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		if ($eid)
		{
			// Update / Create new map
			$this->fileCreator->create();
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
	 * @since   4.0.0
	 */
	public function onExtensionAfterSave($installer, $eid)
	{
		if ($eid)
		{
			// Update / Create new map
			$this->fileCreator->create();
		}
	}
}
