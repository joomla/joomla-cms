<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

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
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

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
		if (!$update_site_id)
		{
			$query->clear();
			$query->insert('#__update_sites');
			$query->columns(array($dbo->quoteName('name'), $dbo->quoteName('type'), $dbo->quoteName('location'), $dbo->quoteName('enabled')));
			$query->values($dbo->quote($name) . ', ' . $dbo->quote($type) . ', ' . $dbo->quote($location) . ', ' . (int) $enabled);
			$dbo->setQuery($query);
			if ($dbo->query())
			{
				// link up this extension to the update site
				$update_site_id = $dbo->insertid();
			}
		}

		// check if it has an update site id (creation might have faileD)
		if ($update_site_id)
		{
			$query->clear();
			// look for an update site entry that exists
			$query->select('update_site_id')->from('#__update_sites_extensions');
			$query->where('update_site_id = '. $update_site_id)->where('extension_id = '. $this->eid);
			$dbo->setQuery($query);
			$tmpid = (int)$dbo->loadResult();
			if(!$tmpid)
			{
				// link this extension to the relevant update site
				$query->clear();
				$query->insert('#__update_sites_extensions');
				$query->columns(array($dbo->quoteName('update_site_id'), $dbo->quoteName('extension_id')));
				$query->values($update_site_id . ', ' . $this->eid);
				$dbo->setQuery($query);
				$dbo->query();
			}
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
		if ($eid)
		{
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
		if ($eid)
		{
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
			$results = $db->loadColumn();

			if(is_array($results))
			{
				// so we need to delete the update sites and their associated updates
				$updatesite_delete = $db->getQuery(true);
				$updatesite_delete->delete()->from('#__update_sites');
				$updatesite_query = $db->getQuery(true);
				$updatesite_query->select('update_site_id')->from('#__update_sites');

				// if we get results back then we can exclude them
				if(count($results))
				{
					$updatesite_query->where('update_site_id NOT IN ('. implode(',', $results) .')');
					$updatesite_delete->where('update_site_id NOT IN ('. implode(',', $results) .')');
				}
				// so lets find what update sites we're about to nuke and remove their associated extensions
				$db->setQuery($updatesite_query);
				$update_sites_pending_delete = $db->loadColumn();
				if(is_array($update_sites_pending_delete) && count($update_sites_pending_delete))
				{
					// nuke any pending updates with this site before we delete it
					// TODO: investigate alternative of using a query after the delete below with a query and not in like above
					$query->clear();
					$query->delete()->from('#__updates')->where('update_site_id IN ('. implode(',', $update_sites_pending_delete) .')');
					$db->setQuery($query);
					$db->query();
				}

				// note: this might wipe out the entire table if there are no extensions linked
				$db->setQuery($updatesite_delete);
				$db->query();

			}

			// last but not least we wipe out any pending updates for the extension
			$query->clear();
			$query->delete()->from('#__updates')->where('extension_id = '. $eid);
			$db->setQuery($query);
			$db->query();
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

		if($updateservers) {
			$children = $updateservers->children();
		} else {
			$children = array();
		}

		if (count($children))
		{
			foreach ($children as $child)
			{
				$attrs = $child->attributes();
				$this->addUpdateSite($attrs['name'], $attrs['type'], $child, true);
			}
		}
		else
		{
			$data = (string)$updateservers;

			if (strlen($data))
			{
				// 	we have a single entry in the update server line, let us presume this is an extension line
				$this->addUpdateSite(JText::_('PLG_EXTENSION_JOOMLA_UNKNOWN_SITE'), 'extension', $data, true);
			}
		}
	}
}
