<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * A helper Model to interact with Joomla!'s extensions update feature
 */
class FOFUtilsUpdate extends FOFModel
{
	/** @var JUpdater The Joomla! updater object */
	protected $updater = null;

	/** @var int The extension_id of this component */
	protected $extension_id = 0;

	/** @var string The currently installed version, as reported by the #__extensions table */
	protected $version = 'dev';

	/** @var string The name of the component e.g. com_something */
	protected $component = 'com_foobar';

	/** @var string The URL to the component's update XML stream */
	protected $updateSite = null;

	/** @var string The name to the component's update site (description of the update XML stream) */
	protected $updateSiteName = null;

	/** @var string The extra query to append to (commercial) components' download URLs */
	protected $extraQuery = null;

	/**
	 * Public constructor. Initialises the protected members as well. Useful $config keys:
	 * update_component		The component name, e.g. com_foobar
	 * update_version		The default version if the manifest cache is unreadable
	 * update_site			The URL to the component's update XML stream
	 * update_extraquery	The extra query to append to (commercial) components' download URLs
	 * update_sitename		The update site's name (description)
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Get an instance of the updater class
		$this->updater = JUpdater::getInstance();

		// Get the component name
		if (isset($config['update_component']))
		{
			$this->component = $config['update_component'];
		}
		else
		{
			$this->component = $this->input->getCmd('option', '');
		}

		// Get the component version
		if (isset($config['update_version']))
		{
			$this->version = $config['update_version'];
		}

		// Get the update site
		if (isset($config['update_site']))
		{
			$this->updateSite = $config['update_site'];
		}

		// Get the extra query
		if (isset($config['update_extraquery']))
		{
			$this->extraQuery = $config['update_extraquery'];
		}

		// Get the extra query
		if (isset($config['update_sitename']))
		{
			$this->updateSiteName = $config['update_sitename'];
		}

		// Find the extension ID
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q($this->component));
		$db->setQuery($query);
		$extension = $db->loadObject();

		if (is_object($extension))
		{
			$this->extension_id = $extension->extension_id;
			$data = json_decode($extension->manifest_cache, true);

			if (isset($data['version']))
			{
				$this->version = $data['version'];
			}
		}
	}

	/**
	 * Retrieves the update information of the component, returning an array with the following keys:
	 *
	 * hasUpdate	True if an update is available
	 * version		The version of the available update
	 * infoURL		The URL to the download page of the update
	 *
	 * @param   bool  $force  Set to true if you want to forcibly reload the update information
	 *
	 * @return  array  See the method description for more information
	 */
	public function getUpdates($force = false)
	{
		$db = $this->getDbo();

		// Default response (no update)
		$updateResponse = array(
			'hasUpdate' => false,
			'version'   => '',
			'infoURL'   => ''
		);

		if (empty($this->extension_id))
		{
			return $updateResponse;
		}

		// If we are forcing the reload, set the last_check_timestamp to 0
		// and remove cached component update info in order to force a reload
		if ($force)
		{
			// Find the update site IDs
			$updateSiteIds = $this->getUpdateSiteIds();

			if (empty($updateSiteIds))
			{
				return $updateResponse;
			}

			// Set the last_check_timestamp to 0
			$query = $db->getQuery(true)
				->update($db->qn('#__update_sites'))
				->set($db->qn('last_check_timestamp') . ' = ' . $db->q('0'))
				->where($db->qn('update_site_id') .' IN ('.implode(', ', $updateSiteIds).')');
			$db->setQuery($query);
			$db->execute();

			// Remove cached component update info from #__updates
			$query = $db->getQuery(true)
				->delete($db->qn('#__updates'))
				->where($db->qn('update_site_id') .' IN ('.implode(', ', $updateSiteIds).')');
			$db->setQuery($query);
			$db->execute();
		}

		// Use the update cache timeout specified in com_installer
		$comInstallerParams = JComponentHelper::getParams('com_installer', false);
		$timeout = 3600 * $comInstallerParams->get('cachetimeout', '6');

		// Load any updates from the network into the #__updates table
		$this->updater->findUpdates($this->extension_id, $timeout);

		// Get the update record from the database
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__updates'))
			->where($db->qn('extension_id') . ' = ' . $db->q($this->extension_id));
		$db->setQuery($query);
		$updateRecord = $db->loadObject();

		// If we have an update record in the database return the information found there
		if (is_object($updateRecord))
		{
			$updateResponse = array(
				'hasUpdate' => true,
				'version'   => $updateRecord->version,
				'infoURL'   => $updateRecord->infourl,
			);
		}

		return $updateResponse;
	}

	/**
	 * Gets the update site Ids for our extension.
	 *
	 * @return 	mixed	An array of Ids or null if the query failed.
	 */
	public function getUpdateSiteIds()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($this->extension_id));
		$db->setQuery($query);
		$updateSiteIds = $db->loadColumn(0);

		return $updateSiteIds;
	}

	/**
	 * Get the currently installed version as reported by the #__extensions table
	 *
	 * @return  string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Override the currently installed version as reported by the #__extensions table
	 *
	 * @param  string  $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		if (empty($this->extension_id))
		{
			return;
		}

		// Create the update site definition we want to store to the database
		$update_site = array(
			'name'		=> $this->updateSiteName,
			'type'		=> 'extension',
			'location'	=> $this->updateSite,
			'enabled'	=> 1,
			'last_check_timestamp'	=> 0,
			'extra_query'	=> $this->extraQuery
		);

		// Get a reference to the db driver
		$db = $this->getDbo();

		// Get the #__update_sites columns
		$columns = $db->getTableColumns('#__update_sites', true);

		if (version_compare(JVERSION, '3.0.0', 'lt') || !array_key_exists('extra_query', $columns))
		{
			unset($update_site['extra_query']);
		}

		// Get the update sites for our extension
		$updateSiteIds = $this->getUpdateSiteIds();

		if (!count($updateSiteIds))
		{
			// No update sites defined. Create a new one.
			$newSite = (object)$update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object)array(
				'update_site_id'	=> $id,
				'extension_id'		=> $this->extension_id,
			);
			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIds as $id)
			{
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				if (empty($aSite))
				{
					// Update site not defined. Create a new one.
					$update_site['update_site_id'] = $id;
					$newSite = (object)$update_site;
					$db->insertObject('#__update_sites', $newSite);

					// Update site is now up-to-date, don't need to refresh it anymore.
					continue;
				}

				// Is it enabled (Joomla! seriously sucks: IT DISABLES UPDATE SITES WITHOUT THE POSSIBILITY TO RE-ENABLE THEM!)
				if ($aSite->enabled)
				{
					// Does the name and location match?
					if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
					{
						// Do we have the extra_query property (J 3.2+) and does it match?
						if (property_exists($aSite, 'extra_query') && isset($update_site['extra_query']))
						{
							if ($aSite->extra_query == $update_site['extra_query'])
							{
								continue;
							}
						}
						else
						{
							// Joomla! 3.1 or earlier. Updates may or may not work.
							continue;
						}
					}
				}

				$update_site['update_site_id'] = $id;
				$newSite = (object)$update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}
	}
}