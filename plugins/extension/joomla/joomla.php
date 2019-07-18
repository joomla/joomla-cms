<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Joomla
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

/**
 * Joomla! master extension plugin.
 *
 * @since  1.6
 */
class PlgExtensionJoomla extends CMSPlugin
{
	/**
	 * Database driver
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

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
		// Look if the location is used already; doesn't matter what type you can't have two types at the same address, doesn't make sense
		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName('update_site_id'))
			->from($this->db->quoteName('#__update_sites'))
			->where($this->db->quoteName('location') . ' = :location')
			->bind(':location', $location);

		$this->db->setQuery($query);

		$update_site_id = (int) $this->db->loadResult();

		// If it doesn't exist, add it!
		if (!$update_site_id)
		{
			$enabled = (int) $enabled;
			$query->clear()
				->insert($this->db->quoteName('#__update_sites'))
				->columns($this->db->quoteName(['name', 'type', 'location', 'enabled']))
				->values(':name, :type, :location, :enabled')
				->bind(':name', $name)
				->bind(':type', $type)
				->bind(':location', $location)
				->bind(':enabled', $enabled, ParameterType::INTEGER);

			$this->db->setQuery($query);

			if ($this->db->execute())
			{
				// Link up this extension to the update site
				$update_site_id = $this->db->insertid();
			}
		}

		// Check if it has an update site id (creation might have failed)
		if ($update_site_id)
		{
			// Look for an update site entry that exists
			$query->clear()
				->select($this->db->quoteName('update_site_id'))
				->from($this->db->quoteName('#__update_sites_extensions'))
				->where(
					[
						$this->db->quoteName('update_site_id') . ' = :updatesiteid',
						$this->db->quoteName('extension_id') . ' = :extensionid',
					]
				)
				->bind(':updatesiteid', $update_site_id, ParameterType::INTEGER)
				->bind(':extensionid', $this->eid, ParameterType::INTEGER);

			$this->db->setQuery($query);

			$tmpid = (int) $this->db->loadResult();

			if (!$tmpid)
			{
				// Link this extension to the relevant update site
				$query->clear()
					->insert($this->db->quoteName('#__update_sites_extensions'))
					->columns($this->db->quoteName(['update_site_id', 'extension_id']))
					->values(':updatesiteid, :eid')
					->bind(':updatesiteid', $update_site_id, ParameterType::INTEGER)
					->bind(':eid', $this->eid, ParameterType::INTEGER);

				$this->db->setQuery($query);
				$this->db->execute();
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
	public function onExtensionAfterInstall($installer, $eid )
	{
		if ($eid)
		{
			$this->installer = $installer;
			$this->eid = (int) $eid;

			// After an install we only need to do update sites
			$this->processUpdateSites();
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
	 * @since   1.6
	 */
	public function onExtensionAfterUninstall($installer, $eid, $removed)
	{
		// If we have a valid extension ID and the extension was successfully uninstalled wipe out any
		// update sites for it
		if ($eid && $removed)
		{
			$query = $this->db->getQuery(true);
			$eid   = (int) $eid;

			$query->delete($this->db->quoteName('#__update_sites_extensions'))
				->where($this->db->quoteName('extension_id') . ' = :eid')
				->bind(':eid', $eid, ParameterType::INTEGER);

			$this->db->setQuery($query);
			$this->db->execute();

			// Delete any unused update sites
			$query->clear()
				->select($this->db->quoteName('update_site_id'))
				->from($this->db->quoteName('#__update_sites_extensions'));

			$this->db->setQuery($query);
			$results = $this->db->loadColumn();

			if (is_array($results))
			{
				// So we need to delete the update sites and their associated updates
				$updatesite_delete = $this->db->getQuery(true);
				$updatesite_delete->delete($this->db->quoteName('#__update_sites'));

				$updatesite_query = $this->db->getQuery(true);
				$updatesite_query->select($this->db->quoteName('update_site_id'))
					->from($this->db->quoteName('#__update_sites'));

				// If we get results back then we can exclude them
				if (count($results))
				{
					$updatesite_query->whereNotIn($this->db->quoteName('update_site_id'), $results);
					$updatesite_delete->whereNotIn($this->db->quoteName('update_site_id'), $results);
				}

				// So let's find what update sites we're about to nuke and remove their associated extensions
				$this->db->setQuery($updatesite_query);
				$update_sites_pending_delete = $this->db->loadColumn();

				if (is_array($update_sites_pending_delete) && count($update_sites_pending_delete))
				{
					// Nuke any pending updates with this site before we delete it
					// TODO: investigate alternative of using a query after the delete below with a query and not in like above
					$query->clear()
						->delete($this->db->quoteName('#__updates'))
						->whereIn($this->db->quoteName('update_site_id'), $update_sites_pending_delete);

					$this->db->setQuery($query);
					$this->db->execute();
				}

				// Note: this might wipe out the entire table if there are no extensions linked
				$this->db->setQuery($updatesite_delete);
				$this->db->execute();
			}

			// Last but not least we wipe out any pending updates for the extension
			$query->clear()
				->delete($this->db->quoteName('#__updates'))
				->where($this->db->quoteName('extension_id') . ' = :eid')
				->bind(':eid', $eid, ParameterType::INTEGER);

			$this->db->setQuery($query);
			$this->db->execute();
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
			$this->eid = (int) $eid;

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
				$this->addUpdateSite(Text::_('PLG_EXTENSION_JOOMLA_UNKNOWN_SITE'), 'extension', $data, true);
			}
		}
	}
}
