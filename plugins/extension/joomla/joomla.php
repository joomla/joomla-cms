<?php
/**
 * @version		$Id: weblinks.php 16731 2010-05-04 05:40:37Z eddieajau $
 * @package		Joomla.Plugin
 * @subpackage	Extension.Joomla
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Joomla! master extension plugin.
 *
 * @package		Joomla.Plugin
 * @subpackage	Extension.Joomla
 * @since		1.6
 */
class plgExtensionJoomla extends JPlugin
{
	/**
	 * @var		integer Extension Identifier
	 * @since	1.6
	 */
	private $eid = 0;

	/**
	 * @var		JInstaller Installer object
	 * @since	1.6
	 */
	private $installer = null;

	/**
	 * Adds an update site to the table if it doesn't exist.
	 *
	 * @param	string	The friendly name of the site
	 * @param	string	The type of site (e.g. collection or extension)
	 * @param	string	The URI for the site
	 * @param	boolean	If this site is enabled
	 * @since	1.6
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
		if (!$update_site_id) {
			$query->clear();
			$query->insert('#__update_sites');
			$query->set('name = ' . $dbo->Quote($name));
			$query->set('type = '. $dbo->Quote($type));
			$query->set('location = '. $dbo->Quote($location));
			$query->set('enabled = '. (int)$enabled);
			$dbo->setQuery($query);
			if ($dbo->query()) {
				// link up this extension to the update site
				$update_site_id = $dbo->insertid();
			}
		}

		// check if it has an update site id (creation might have faileD)
		if ($update_site_id) {
			// link this extension to the relevant update site
			$query->clear();
			$query->insert('#__update_sites_extensions');
			$query->set('update_site_id = '. $update_site_id);
			$query->set('extension_id = '. $this->eid);
			$dbo->setQuery($query);
			$dbo->query();
		}
	}

	/**
	 * Handle post extension install update sites
	 *
	 * @param	JInstaller	Installer object
	 * @param	int			Extension Identifier
	 * @since	1.6
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		if ($eid) {
			$this->installer = $installer;
			$this->eid = $eid;

			// After an install we only need to do update sites
			$this->processUpdateSites();
		}
	}

	/**
	 * Handle extension uninstall
	 *
	 * @param	JInstaller	Installer instance
	 * @param	int			extension id
	 * @param	int			installation result
	 * @since	1.6
	 */
	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		if ($eid) {
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
	 * After update of an extension
	 *
	 * @param	JInstaller	Installer object
	 * @param	int			Extension identifier
	 * @since	1.6
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		if ($eid) {
			$this->installer = $installer;
			$this->eid = $eid;

			// handle any update sites
			$this->processUpdateSites();
		}
	}

	/**
	 * Processes the list of update sites for an extension.
	 *
	 * @since	1.6
	 */
	private function processUpdateSites()
	{
		$manifest		= $this->installer->getManifest();
		$updateservers	= $manifest->updateservers;

		if (is_a($updateservers, 'JXMLElement')) {
			$children = $updateservers->children();

			if (count($children)) {
				foreach ($children as $child) {
					$attrs = $child->attributes();
					$this->addUpdateSite($attrs['name'], $attrs['type'], $child, true);
				}
			} else {
				$data = (string)$updateservers;

				if (strlen($data)) {
					// 	we have a single entry in the update server line, let us presume this is an extension line
					$this->addUpdateSite(JText::_('PLG_EXTENSION_JOOMLA_UNKNOWN_SITE'), 'extension', $data, true);
				}
			}
		}
	}
}