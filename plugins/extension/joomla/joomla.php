<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Joomla
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! master extension plugin.
 *
 * @since  1.6
 */
class PlgExtensionJoomla extends JPlugin
{
	/**
	 * @var    integer Extension Identifier
	 * @since  1.6
	 */
	private $eid = 0;

	/**
	 * @var    JInstaller Installer object
	 * @since  1.6
	 */
	private $installer = null;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds an update site to the table if it doesn't exist.
	 *
	 * @param   string   $name      The friendly name of the site
	 * @param   string   $type      The type of site (e.g. collection or extension)
	 * @param   string   $location  The URI for the site
	 * @param   boolean  $enabled   If this site is enabled
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	private function addUpdateSite($name, $type, $location, $enabled)
	{
		$db = JFactory::getDbo();

		// Look if the location is used already; doesn't matter what type you can't have two types at the same address, doesn't make sense
		$query = $db->getQuery(true)
			->select('update_site_id')
			->from('#__update_sites')
			->where('location = ' . $db->quote($location));
		$db->setQuery($query);
		$update_site_id = (int) $db->loadResult();

		// If it doesn't exist, add it!
		if (!$update_site_id)
		{
			$query->clear()
				->insert('#__update_sites')
				->columns(array($db->quoteName('name'), $db->quoteName('type'), $db->quoteName('location'), $db->quoteName('enabled')))
				->values($db->quote($name) . ', ' . $db->quote($type) . ', ' . $db->quote($location) . ', ' . (int) $enabled);
			$db->setQuery($query);

			if ($db->execute())
			{
				// Link up this extension to the update site
				$update_site_id = $db->insertid();
			}
		}

		// Check if it has an update site id (creation might have failed)
		if ($update_site_id)
		{
			// Look for an update site entry that exists
			$query->clear()
				->select('update_site_id')
				->from('#__update_sites_extensions')
				->where('update_site_id = ' . $update_site_id)
				->where('extension_id = ' . $this->eid);
			$db->setQuery($query);
			$tmpid = (int) $db->loadResult();

			if (!$tmpid)
			{
				// Link this extension to the relevant update site
				$query->clear()
					->insert('#__update_sites_extensions')
					->columns(array($db->quoteName('update_site_id'), $db->quoteName('extension_id')))
					->values($update_site_id . ', ' . $this->eid);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Handle post extension install update sites
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   1.6
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
	 * @param   JInstaller  $installer  Installer instance
	 * @param   integer     $eid        Extension id
	 * @param   boolean     $result     Installation result
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		// If we have a valid extension ID and the extension was successfully uninstalled wipe out any
		// update sites for it
		if ($eid && $result)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->delete('#__update_sites_extensions')
				->where('extension_id = ' . $eid);
			$db->setQuery($query);
			$db->execute();

			// Delete any unused update sites
			$query->clear()
				->select('update_site_id')
				->from('#__update_sites_extensions');
			$db->setQuery($query);
			$results = $db->loadColumn();

			if (is_array($results))
			{
				// So we need to delete the update sites and their associated updates
				$updatesite_delete = $db->getQuery(true);
				$updatesite_delete->delete('#__update_sites');
				$updatesite_query = $db->getQuery(true);
				$updatesite_query->select('update_site_id')
					->from('#__update_sites');

				// If we get results back then we can exclude them
				if (count($results))
				{
					$updatesite_query->where('update_site_id NOT IN (' . implode(',', $results) . ')');
					$updatesite_delete->where('update_site_id NOT IN (' . implode(',', $results) . ')');
				}

				// So let's find what update sites we're about to nuke and remove their associated extensions
				$db->setQuery($updatesite_query);
				$update_sites_pending_delete = $db->loadColumn();

				if (is_array($update_sites_pending_delete) && count($update_sites_pending_delete))
				{
					// Nuke any pending updates with this site before we delete it
					// TODO: investigate alternative of using a query after the delete below with a query and not in like above
					$query->clear()
						->delete('#__updates')
						->where('update_site_id IN (' . implode(',', $update_sites_pending_delete) . ')');
					$db->setQuery($query);
					$db->execute();
				}

				// Note: this might wipe out the entire table if there are no extensions linked
				$db->setQuery($updatesite_delete);
				$db->execute();
			}

			// Last but not least we wipe out any pending updates for the extension
			$query->clear()
				->delete('#__updates')
				->where('extension_id = ' . $eid);
			$db->setQuery($query);
			$db->execute();
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
	 * @since   1.6
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		if ($eid)
		{
			$this->installer = $installer;
			$this->eid = $eid;

			// Handle any update sites
			$this->processUpdateSites();
		}
	}

	/**
	 * Processes the list of update sites for an extension.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	private function processUpdateSites()
	{
		$manifest      = $this->installer->getManifest();
		$updateservers = $manifest->updateservers;

		if ($updateservers)
		{
			$children = $updateservers->children();
		}
		else
		{
			$children = array();
		}

		if (count($children))
		{
			foreach ($children as $child)
			{
				$attrs = $child->attributes();
				$this->addUpdateSite($attrs['name'], $attrs['type'], trim($child), true);
			}
		}
		else
		{
			$data = trim((string) $updateservers);

			if ($data !== '')
			{
				// We have a single entry in the update server line, let us presume this is an extension line
				$this->addUpdateSite(JText::_('PLG_EXTENSION_JOOMLA_UNKNOWN_SITE'), 'extension', $data, true);
			}
		}
	}
}
