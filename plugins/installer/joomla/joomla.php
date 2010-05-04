<?php
/**
 * @version		$Id: example.php 16235 2010-04-20 04:13:25Z pasamio $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

/**
 * Joomla! Installer Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since		1.6
 */
class plgInstallerJoomla extends JPlugin
{
	/** @var eid integer Extension Identifier */
	private $eid = 0;

	/** @var installer JInstaller Installer object */
	private $installer = null;


	/**
	 * After update of an extension
	 * @param JInstaller $installer Installer object
	 * @param int $eid Extension identifier
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		if($eid)
		{
			$this->installer = $installer;
			$this->eid = $eid;

			// handle any update sites
			$this->processUpdateSites();
		}
	}

	/**
	 * Handle post extension install update sites
	 * @param JInstaller $installer Installer object
	 * @param int $eid Extension Identifier
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		if($eid) {
			$this->installer = $installer;
			$this->eid = $eid;

			// After an install we only need to do update sites
			$this->processUpdateSites();
		}
	}
	
	/**
	 * Handle extension uninstall
	 * @param JInstaller $installer Installer instance
	 * @param int $eid extension id
	 * @param int $result installation result
	 */
	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		if($eid) {
			// wipe out any update_sites_extensions links
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->delete()->from('#__update_sites_extensions')->where('extension_id = '. $eid);
			$db->setQuery($query);
			$db->Query();
			
			// delete any unused update sites
			$query->clear();
			$query->select('update_site_id')->from('#__update_sites_extensions');
			$db->setQuery($query);
			$results = $db->loadResultArray();
			
			if(is_array($results)) {
				$query->clear();
				$query->delete()->from('#__update_sites');
				// if we get results back then we can exclude them
				if(count($results)) {
					$query->where('update_site_id NOT IN ('. implode(',', $results) .')');
				}
				// note: this wipes out the entire table since there are no extensions linked
				$db->setQuery($query);
				$db->query();			
			}
		}
	}


	/**
	 * Processes the list of update sites for an extension
	 */
	private function processUpdateSites()
	{
		$manifest = $this->installer->getManifest();

		$updateservers = $manifest->updateservers;
		if(is_a($updateservers, 'JXMLElement'))
		{
			$children = $updateservers->children();
			if(count($children))
			{
				foreach($children as $child)
				{
					$attrs = $child->attributes();
					$this->addUpdateSite($attrs['name'], $attrs['type'], $child, true);
				}
			}
			else
			{
				$data = (string)$updateservers;
				if(strlen($data))
				{
					// 	we have a single entry in the update server line, let us presume this is an extension line
					$this->addUpdateSite(JText::_('PLG_EXTENSION_JOOMLA_UNKNOWN_SITE'), 'extension', $data, true);
				}
			}
		}
	}


	/**
	 * Adds an update site to the table if it doesn't exist
	 * @param string $name The friendly name of the site
	 * @param string $type The type of site (e.g. collection or extension)
	 * @param string $location The URI for the site
	 * @param boolean $enabled If this site is enabled
	 */
	private function addUpdateSite($name, $type, $location, $enabled)
	{
		$dbo = JFactory::getDBO();
		// look if the location is used already; doesn't matter what type
		// you can't have two types at the same address, doesn't make sense
		$query = $dbo->getQuery(true);
		$query->select('update_site_id')->from('#__update_sites')->where('location = '. $dbo->Quote($location));
		$dbo->setQuery($query);
		$update_site_id = (int)$dbo->loadResult();

		// if it doesn't exist, add it!
		if(!$update_site_id)
		{
			$query->clear();
			$query->insert('#__update_sites');
			$query->set('name = ' . $dbo->Quote($name));
			$query->set('type = '. $dbo->Quote($type));
			$query->set('location = '. $dbo->Quote($location));
			$query->set('enabled = '. (int)$enabled);
			$dbo->setQuery($query);
			if($dbo->query()) {
				// link up this extension to the update site
				$update_site_id = $dbo->insertid();
			}
		}

		// check if it has an update site id (creation might have faileD)
		if($update_site_id) {
			// link this extension to the relevant update site
			$query->clear();
			$query->insert('#__update_sites_extensions');
			$query->set('update_site_id = '. $update_site_id);
			$query->set('extension_id = '. $this->eid);
			$dbo->setQuery($query);
			$dbo->query();
		}
	}
}