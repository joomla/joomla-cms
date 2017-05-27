<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

if (version_compare(JVERSION, '2.5.0', 'lt'))
{
	jimport('joomla.updater.updater');
}

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

	/** @var string The machine readable name of the component e.g. com_something */
	protected $component = 'com_foobar';

	/** @var string The human readable name of the component e.g. Your Component's Name. Used for emails. */
	protected $componentDescription = 'Foobar';

	/** @var string The URL to the component's update XML stream */
	protected $updateSite = null;

	/** @var string The name to the component's update site (description of the update XML stream) */
	protected $updateSiteName = null;

	/** @var string The extra query to append to (commercial) components' download URLs */
	protected $extraQuery = null;

	/** @var string The common parameters' key, used for storing data in the #__akeeba_common table */
	protected $commonKey = 'foobar';

	/**
	 * The common parameters table. It's a simple table with key(VARCHAR) and value(LONGTEXT) fields.
	 * Here is an example MySQL CREATE TABLE command to make this kind of table:
	 *
	 * CREATE TABLE `#__akeeba_common` (
	 * 	`key` varchar(255) NOT NULL,
	 * 	`value` longtext NOT NULL,
	 * 	PRIMARY KEY (`key`)
	 * 	) DEFAULT COLLATE utf8_general_ci CHARSET=utf8;
	 *
	 * @var  string
	 */
	protected $commonTable = '#__akeeba_common';

	/**
	 * Subject of the component update emails
	 *
	 * @var  string
	 */
	protected $updateEmailSubject = 'THIS EMAIL IS SENT FROM YOUR SITE "[SITENAME]" - Update available for [COMPONENT], new version [VERSION]';

	/**
	 * Body of the component update email
	 *
	 * @var  string
	 */
	protected $updateEmailBody= <<< ENDBLOCK
This email IS NOT sent by the authors of [COMPONENT].
It is sent automatically by your own site, [SITENAME].

================================================================================
UPDATE INFORMATION
================================================================================

Your site has determined that there is an updated version of [COMPONENT]
available for download.

New version number: [VERSION]

This email is sent to you by your site to remind you of this fact. The authors
of the software will never contact you about available updates.

================================================================================
WHY AM I RECEIVING THIS EMAIL?
================================================================================

This email has been automatically sent by a CLI script or Joomla! plugin you, or
the person who built or manages your site, has installed and explicitly
activated. This script or plugin looks for updated versions of the software and
sends an email notification to all Super Users. You will receive several similar
emails from your site, up to 6 times per day, until you either update the
software or disable these emails.

To disable these emails, please contact your site administrator.

If you do not understand what this means, please do not contact the authors of
the software. They are NOT sending you this email and they cannot help you.
Instead, please contact the person who built or manages your site.

================================================================================
WHO SENT ME THIS EMAIL?
================================================================================

This email is sent to you by your own site, [SITENAME]
ENDBLOCK;

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

		// Get the component description
		if (isset($config['update_component_description']))
		{
			$this->component = $config['update_component_description'];
		}
		else
		{
			// Try to auto-translate (hopefully you've loaded the language files)
			$key = strtoupper($this->component);
			$description = JText::_($key);
		}

		// Get the component version
		if (isset($config['update_version']))
		{
			$this->version = $config['update_version'];
		}

		// Get the common key
		if (isset($config['common_key']))
		{
			$this->commonKey = $config['common_key'];
		}
		else
		{
			// Convert com_foobar, pkg_foobar etc to "foobar"
			$this->commonKey = substr($this->component, 4);
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

		// Get the update site's name
		if (isset($config['update_sitename']))
		{
			$this->updateSiteName = $config['update_sitename'];
		}

		// Find the extension ID
		$db = FOFPlatform::getInstance()->getDbo();
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
	 * @param   bool    $force            Set to true if you want to forcibly reload the update information
	 * @param   string  $preferredMethod  Preferred update method: 'joomla' or 'classic'
	 *
	 * @return  array  See the method description for more information
	 */
	public function getUpdates($force = false, $preferredMethod = null)
	{
		// Default response (no update)
		$updateResponse = array(
			'hasUpdate' => false,
			'version'   => '',
			'infoURL'   => '',
			'downloadURL' => '',
		);

		if (empty($this->extension_id))
		{
			return $updateResponse;
		}

		$updateRecord = $this->findUpdates($force, $preferredMethod);

		// If we have an update record in the database return the information found there
		if (is_object($updateRecord))
		{
			$updateResponse = array(
				'hasUpdate' => true,
				'version'   => $updateRecord->version,
				'infoURL'   => $updateRecord->infourl,
				'downloadURL' => $updateRecord->downloadurl,
			);
		}

		return $updateResponse;
	}

	/**
	 * Find the available update record object. If we're at the latest version it will return null.
	 *
	 * Please see getUpdateMethod for information on how the $preferredMethod is handled and what it means.
	 *
	 * @param   bool    $force            Should I forcibly reload the updates from the server?
	 * @param   string  $preferredMethod  Preferred update method: 'joomla' or 'classic'
	 *
	 * @return  \stdClass|null
	 */
	public function findUpdates($force, $preferredMethod = null)
	{
		$preferredMethod = $this->getUpdateMethod($preferredMethod);

		switch ($preferredMethod)
		{
			case 'joomla':
				return $this->findUpdatesJoomla($force);
				break;

			default:
			case 'classic':
				return $this->findUpdatesClassic($force);
				break;
		}
	}

	/**
	 * Gets the update site Ids for our extension.
	 *
	 * @return 	mixed	An array of Ids or null if the query failed.
	 */
	public function getUpdateSiteIds()
	{
		$db = FOFPlatform::getInstance()->getDbo();
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
	 * Returns the name of the component, e.g. com_foobar
	 *
	 * @return  string
	 */
	public function getComponentName()
	{
		return $this->component;
	}

	/**
	 * Returns the human readable component name, e.g. Foobar Component
	 *
	 * @return  string
	 */
	public function getComponentDescription()
	{
		return $this->componentDescription;
	}

	/**
	 * Returns the numeric extension ID for the component
	 *
	 * @return  int
	 */
	public function getExtensionId()
	{
		return $this->extension_id;
	}

	/**
	 * Returns the update site URL, i.e. the URL to the XML update stream
	 *
	 * @return  string
	 */
	public function getUpdateSite()
	{
		return $this->updateSite;
	}

	/**
	 * Returns the human readable description of the update site
	 *
	 * @return  string
	 */
	public function getUpdateSiteName()
	{
		return $this->updateSiteName;
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
		// Joomla! 1.5 does not have update sites.
		if (version_compare(JVERSION, '1.6.0', 'lt'))
		{
			return;
		}

		if (empty($this->extension_id))
		{
			return;
		}

		// Remove obsolete update sites that don't match our extension ID but match our name or update site location
		$this->removeObsoleteUpdateSites();

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
		$db = FOFPlatform::getInstance()->getDbo();

		// Get the #__update_sites columns
		$columns = $db->getTableColumns('#__update_sites', true);

		if (version_compare(JVERSION, '3.2.0', 'lt') || !array_key_exists('extra_query', $columns))
		{
			unset($update_site['extra_query']);
		}

		if (version_compare(JVERSION, '2.5.0', 'lt') || !array_key_exists('extra_query', $columns))
		{
			unset($update_site['last_check_timestamp']);
		}

		// Get the update sites for our extension
		$updateSiteIds = $this->getUpdateSiteIds();

		if (empty($updateSiteIds))
		{
			$updateSiteIds = array();
		}

		/** @var boolean $needNewUpdateSite Do I need to create a new update site? */
		$needNewUpdateSite = true;

		/** @var int[] $deleteOldSites Old Site IDs to delete */
		$deleteOldSites = array();

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
				// Update site is now up-to-date, don't need to refresh it anymore.
				continue;
			}

			// We have an update site that looks like ours
			if ($needNewUpdateSite && ($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
			{
				$needNewUpdateSite = false;
				$mustUpdate = false;

				// Is it enabled? If not, enable it.
				if (!$aSite->enabled)
				{
					$mustUpdate = true;
					$aSite->enabled = 1;
				}

				// Do we have the extra_query property (J 3.2+) and does it match?
				if (property_exists($aSite, 'extra_query') && isset($update_site['extra_query'])
					&& ($aSite->extra_query != $update_site['extra_query']))
				{
					$mustUpdate = true;
					$aSite->extra_query = $update_site['extra_query'];
				}

				// Update the update site if necessary
				if ($mustUpdate)
				{
					$db->updateObject('#__update_sites', $aSite, 'update_site_id', true);
				}

				continue;
			}

			// In any other case we need to delete this update site, it's obsolete
			$deleteOldSites[] = $aSite->update_site_id;
		}

		if (!empty($deleteOldSites))
		{
			try
			{
				$obsoleteIDsQuoted = array_map(array($db, 'quote'), $deleteOldSites);

				// Delete update sites
				$query = $db->getQuery(true)
							->delete('#__update_sites')
							->where($db->qn('update_site_id') . ' IN (' . implode(',', $obsoleteIDsQuoted) . ')');
				$db->setQuery($query)->execute();

				// Delete update sites to extension ID records
				$query = $db->getQuery(true)
							->delete('#__update_sites_extensions')
							->where($db->qn('update_site_id') . ' IN (' . implode(',', $obsoleteIDsQuoted) . ')');
				$db->setQuery($query)->execute();
			}
			catch (\Exception $e)
			{
				// Do nothing on failure
				return;
			}

		}

		// Do we still need to create a new update site?
		if ($needNewUpdateSite)
		{
			// No update sites defined. Create a new one.
			$newSite = (object)$update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id                  = $db->insertid();
			$updateSiteExtension = (object)array(
				'update_site_id' => $id,
				'extension_id'   => $this->extension_id,
			);
			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
	}

	/**
	 * Removes any update sites which go by the same name or the same location as our update site but do not match the
	 * extension ID.
	 */
	public function removeObsoleteUpdateSites()
	{
		$db = $this->getDbo();

		// Get update site IDs
		$updateSiteIDs = $this->getUpdateSiteIds();

		// Find update sites where the name OR the location matches BUT they are not one of the update site IDs
		$query = $db->getQuery(true)
					->select($db->qn('update_site_id'))
					->from($db->qn('#__update_sites'))
					->where(
						'((' . $db->qn('name') . ' = ' . $db->q($this->updateSiteName) . ') OR ' .
						'(' . $db->qn('location') . ' = ' . $db->q($this->updateSite) . '))'
					);

		if (!empty($updateSiteIDs))
		{
			$updateSitesQuoted = array_map(array($db, 'quote'), $updateSiteIDs);
			$query->where($db->qn('update_site_id') . ' NOT IN (' . implode(',', $updateSitesQuoted) . ')');
		}

		try
		{
			$ids = $db->setQuery($query)->loadColumn();

			if (!empty($ids))
			{
				$obsoleteIDsQuoted = array_map(array($db, 'quote'), $ids);

				// Delete update sites
				$query = $db->getQuery(true)
							->delete('#__update_sites')
							->where($db->qn('update_site_id') . ' IN (' . implode(',', $obsoleteIDsQuoted) . ')');
				$db->setQuery($query)->execute();

				// Delete update sites to extension ID records
				$query = $db->getQuery(true)
							->delete('#__update_sites_extensions')
							->where($db->qn('update_site_id') . ' IN (' . implode(',', $obsoleteIDsQuoted) . ')');
				$db->setQuery($query)->execute();
			}
		}
		catch (\Exception $e)
		{
			// Do nothing on failure
			return;
		}
	}

	/**
	 * Get the update method we should use, 'joomla' or 'classic'
	 *
	 * You can defined the preferred update method: 'joomla' uses JUpdater whereas 'classic' handles update caching and
	 * parsing internally. If you are on Joomla! 3.1 or earlier this option is forced to 'classic' since these old
	 * Joomla! versions couldn't handle updates of commercial components correctly (that's why I contributed the fix to
	 * that problem, the extra_query field that's present in Joomla! 3.2 onwards).
	 *
	 * If 'classic' is defined then it will be used in *all* Joomla! versions. It's the most stable method for fetching
	 * update information.
	 *
	 * @param   string  $preferred  Preferred update method. One of 'joomla' or 'classic'.
	 *
	 * @return  string
	 */
	public function getUpdateMethod($preferred = null)
	{
		$method = $preferred;

		// Make sure the update fetch method is valid, otherwise load the component's "update_method" parameter.
		$validMethods = array('joomla', 'classic');

		if (!in_array($method, $validMethods))
		{
			$method = FOFUtilsConfigHelper::getComponentConfigurationValue($this->component, 'update_method', 'joomla');
		}

		// We can't handle updates using Joomla!'s extensions updater in Joomla! 3.1 and earlier
		if (($method == 'joomla') && version_compare(JVERSION, '3.2.0', 'lt'))
		{
			$method = 'classic';
		}

		return $method;
	}

	/**
	 * Find the available update record object. If we're at the latest version it will return null.
	 *
	 * @param   bool  $force  Should I forcibly reload the updates from the server?
	 *
	 * @return  \stdClass|null
	 */
	protected function findUpdatesJoomla($force = false)
	{
		$db = FOFPlatform::getInstance()->getDbo();

		// If we are forcing the reload, set the last_check_timestamp to 0
		// and remove cached component update info in order to force a reload
		if ($force)
		{
			// Find the update site IDs
			$updateSiteIds = $this->getUpdateSiteIds();

			if (empty($updateSiteIds))
			{
				return null;
			}

			// Set the last_check_timestamp to 0
			if (version_compare(JVERSION, '2.5.0', 'ge'))
			{
				$query = $db->getQuery(true)
							->update($db->qn('#__update_sites'))
							->set($db->qn('last_check_timestamp') . ' = ' . $db->q('0'))
							->where($db->qn('update_site_id') .' IN ('.implode(', ', $updateSiteIds).')');
				$db->setQuery($query);
				$db->execute();
			}

			// Remove cached component update info from #__updates
			$query = $db->getQuery(true)
						->delete($db->qn('#__updates'))
						->where($db->qn('update_site_id') .' IN ('.implode(', ', $updateSiteIds).')');
			$db->setQuery($query);
			$db->execute();
		}

		// Use the update cache timeout specified in com_installer
		$timeout = 3600 * FOFUtilsConfigHelper::getComponentConfigurationValue('com_installer', 'cachetimeout', '6');

		// Load any updates from the network into the #__updates table
		$this->updater->findUpdates($this->extension_id, $timeout);

		// Get the update record from the database
		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__updates'))
					->where($db->qn('extension_id') . ' = ' . $db->q($this->extension_id));
		$db->setQuery($query);

		try
		{
			$updateObject = $db->loadObject();
		}
		catch (Exception $e)
		{
			return null;
		}

		if (!is_object($updateObject))
		{
			return null;
		}

		$updateObject->downloadurl = '';

		JLoader::import('joomla.updater.update');

		if (class_exists('JUpdate'))
		{
			$update = new JUpdate();
			$update->loadFromXML($updateObject->detailsurl);

			if (isset($update->get('downloadurl')->_data))
			{
				$url = trim($update->downloadurl->_data);

				$extra_query = isset($updateObject->extra_query) ? $updateObject->extra_query : $this->extraQuery;

				if ($extra_query)
				{
					if (strpos($url, '?') === false)
					{
						$url .= '?';
					}
					else
					{
						$url .= '&amp;';
					}

					$url .= $extra_query;
				}

				$updateObject->downloadurl = $url;
			}
		}

		return $updateObject;
	}

	/**
	 * Find the available update record object. If we're at the latest version return null.
	 *
	 * @param   bool  $force  Should I forcibly reload the updates from the server?
	 *
	 * @return  \stdClass|null
	 */
	protected function findUpdatesClassic($force = false)
	{
		$allUpdates = $this->loadUpdatesClassic($force);

		if (empty($allUpdates))
		{
			return null;
		}

		$bestVersion = '0.0.0';
		$bestUpdate = null;
		$bestUpdateObject = null;

		foreach($allUpdates as $update)
		{
			if (!isset($update['version']))
			{
				continue;
			}

			if (version_compare($bestVersion, $update['version'], 'lt'))
			{
				$bestVersion = $update['version'];
				$bestUpdate = $update;
			}
		}

        // If the current version is newer or equal to the best one, unset it. Otherwise the user will be always prompted to update
        if(version_compare($this->version, $bestVersion, 'ge'))
        {
            $bestUpdate = null;
            $bestVersion = '0.0.0';
        }

		if (!is_null($bestUpdate))
		{
			$url = '';

			if (isset($bestUpdate['downloads']) && isset($bestUpdate['downloads'][0])
			&& isset($bestUpdate['downloads'][0]['url']))
			{
				$url = $bestUpdate['downloads'][0]['url'];
			}

			if ($this->extraQuery)
			{
				if (strpos($url, '?') === false)
				{
					$url .= '?';
				}
				else
				{
					$url .= '&amp;';
				}

				$url .= $this->extraQuery;
			}

			$bestUpdateObject = (object)array(
					'update_id'      => 0,
					'update_site_id' => 0,
					'extension_id'   => $this->extension_id,
					'name'           => $this->updateSiteName,
					'description'    => $bestUpdate['description'],
					'element'        => $bestUpdate['element'],
					'type'           => $bestUpdate['type'],
					'folder'         => count($bestUpdate['folder']) ? $bestUpdate['folder'][0] : '',
					'client_id'      => isset($bestUpdate['client']) ? $bestUpdate['client'] : 0,
					'version'        => $bestUpdate['version'],
					'data'           => '',
					'detailsurl'     => $this->updateSite,
					'infourl'        => $bestUpdate['infourl']['url'],
					'extra_query'    => $this->extraQuery,
					'downloadurl'	 => $url,
			);
		}

		return $bestUpdateObject;
	}

	/**
	 * Load all available updates without going through JUpdate
	 *
	 * @param   bool  $force  Should I forcibly reload the updates from the server?
	 *
	 * @return  array
	 */
	protected function loadUpdatesClassic($force = false)
	{
		// Is the cache busted? If it is I set $force = true to make sure I download fresh updates
		if (!$force)
		{
			// Get the cache timeout. On older Joomla! installations it will always default to 6 hours.
			$timeout = 3600 * FOFUtilsConfigHelper::getComponentConfigurationValue('com_installer', 'cachetimeout', '6');

			// Do I need to check for updates?
			$lastCheck = $this->getCommonParameter('lastcheck', 0);
			$now = time();

			if (($now - $lastCheck) >= $timeout)
			{
				$force = true;
			}
		}

		// Get the cached JSON-encoded updates list
		$rawUpdates = $this->getCommonParameter('allUpdates', '');

		// Am I forced to reload the XML file (explicitly or because the cache is busted)?
		if ($force)
		{
			// Set the timestamp
			$now = time();
			$this->setCommonParameter('lastcheck', $now);

			// Get all available updates
			$updateHelper = new FOFUtilsUpdateExtension();
			$updates = $updateHelper->getUpdatesFromExtension($this->updateSite);

			// Save the raw updates list in the database
			$rawUpdates = json_encode($updates);
			$this->setCommonParameter('allUpdates', $rawUpdates);
		}

		// Decode the updates list
		$updates = json_decode($rawUpdates, true);

		// Walk through the updates and find the ones compatible with our Joomla! and PHP version
		$compatibleUpdates = array();

		// Get the Joomla! version family (e.g. 2.5)
		$jVersion = JVERSION;
		$jVersionParts = explode('.', $jVersion);
		$jVersionShort = $jVersionParts[0] . '.' . $jVersionParts[1];

		// Get the PHP version family (e.g. 5.6)
		$phpVersion = PHP_VERSION;
		$phpVersionParts = explode('.', $phpVersion);
		$phpVersionShort = $phpVersionParts[0] . '.' . $phpVersionParts[1];

		foreach ($updates as $update)
		{
			// No platform?
			if (!isset($update['targetplatform']))
			{
				continue;
			}

			// Wrong platform?
			if ($update['targetplatform']['name'] != 'joomla')
			{
				continue;
			}

			// Get the target Joomla! version
			$targetJoomlaVersion = $update['targetplatform']['version'];
			$targetVersionParts = explode('.', $targetJoomlaVersion);
			$targetVersionShort = $targetVersionParts[0] . '.' . $targetVersionParts[1];

			// The target version MUST be in the same Joomla! branch
			if ($jVersionShort != $targetVersionShort)
			{
				continue;
			}

			// If the target version is major.minor.revision we must make sure our current JVERSION is AT LEAST equal to that.
			if (version_compare($targetJoomlaVersion, JVERSION, 'gt'))
			{
				continue;
			}

			// Do I have target PHP versions?
			if (isset($update['ars-phpcompat']))
			{
				$phpCompatible = false;

				foreach ($update['ars-phpcompat'] as $entry)
				{
					// Get the target PHP version family
					$targetPHPVersion = $entry['@attributes']['version'];
					$targetPHPVersionParts = explode('.', $targetPHPVersion);
					$targetPHPVersionShort = $targetPHPVersionParts[0] . '.' . $targetPHPVersionParts[1];

					// The target PHP version MUST be in the same PHP branch
					if ($phpVersionShort != $targetPHPVersionShort)
					{
						continue;
					}

					// If the target version is major.minor.revision we must make sure our current PHP_VERSION is AT LEAST equal to that.
					if (version_compare($targetPHPVersion, PHP_VERSION, 'gt'))
					{
						continue;
					}

					$phpCompatible = true;
					break;
				}

				if (!$phpCompatible)
				{
					continue;
				}
			}

			// All checks pass. Add this update to the list of compatible updates.
			$compatibleUpdates[] = $update;
		}

		return $compatibleUpdates;
	}

	/**
	 * Get a common parameter from the #__akeeba_common table
	 *
	 * @param   string  $key      The key to retrieve
	 * @param   mixed   $default  The default value in case none is set
	 *
	 * @return  mixed  The saved parameter value (or $default, if nothing is currently set)
	 */
	protected function getCommonParameter($key, $default = null)
	{
		$dbKey = $this->commonKey . '_autoupdate_' . $key;

		$db = FOFPlatform::getInstance()->getDbo();

		$query = $db->getQuery(true)
					->select($db->qn('value'))
					->from($db->qn($this->commonTable))
					->where($db->qn('key') . ' = ' . $db->q($dbKey));

		$result = $db->setQuery($query)->loadResult();

		if (!$result)
		{
			return $default;
		}

		return $result;
	}

	/**
	 * Set a common parameter from the #__akeeba_common table
	 *
	 * @param   string  $key    The key to set
	 * @param   mixed   $value  The value to set
	 *
	 * @return  void
	 */
	protected function setCommonParameter($key, $value)
	{
		$dbKey = $this->commonKey . '_autoupdate_' . $key;

		$db = FOFPlatform::getInstance()->getDbo();

		$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn($this->commonTable))
					->where($db->qn('key') . ' = ' . $db->q($dbKey));
		$count = $db->setQuery($query)->loadResult();

		if ($count)
		{
			$query = $db->getQuery(true)
						->update($db->qn($this->commonTable))
						->set($db->qn('value') . ' = ' . $db->q($value))
						->where($db->qn('key') . ' = ' . $db->q($dbKey));
			$db->setQuery($query)->execute();
		}
		else
		{
			$data = (object)array(
					'key'   => $dbKey,
					'value' => $value,
			);

			$db->insertObject($this->commonTable, $data);
		}
	}

	/**
	 * Proxy to updateComponent(). Required since old versions of our software had an updateComponent method declared
	 * private. If we set the updateComponent() method public we cause a fatal error.
	 *
	 * @return  string
	 */
	public function doUpdateComponent()
	{
		return $this->updateComponent();
	}

	/**
	 * Automatically install the extension update under Joomla! 1.5.5 or later (web) / 3.0 or later (CLI).
	 *
	 * @return  string  The update message
	 */
	private function updateComponent()
	{
		$isCli          = FOFPlatform::getInstance()->isCli();
		$minVersion     = $isCli ? '3.0.0' : '1.5.5';
		$errorQualifier = $isCli ? ' using an unattended CLI CRON script ' : ' ';

		if (version_compare(JVERSION, $minVersion, 'lt'))
		{
			return "Extension updates{$errorQualifier}only work with Joomla! $minVersion and later.";
		}

		try
		{
			$updatePackagePath = $this->downloadUpdate();
		}
		catch (Exception $e)
		{
			return $e->getMessage();
		}

		// Unpack the downloaded package file
		jimport('joomla.installer.helper');
		jimport('cms.installer.helper');
		$package = JInstallerHelper::unpack($updatePackagePath);

		if (!$package)
		{
			// Clean up
			if (JFile::exists($updatePackagePath))
			{
				JFile::delete($updatePackagePath);
			}

			return "An error occurred while unpacking the file. Please double check your Joomla temp-directory setting in Global Configuration.";
		}

		$installer = new JInstaller;
		$installed = $installer->install($package['extractdir']);

		// Let's cleanup the downloaded archive and the temp folder
		if (JFolder::exists($package['extractdir']))
		{
			JFolder::delete($package['extractdir']);
		}

		if (JFile::exists($package['packagefile']))
		{
			JFile::delete($package['packagefile']);
		}

		if ($installed)
		{
			return "Component successfully updated";
		}
		else
		{
			return "An error occurred while trying to update the component";
		}
	}

	/**
	 * Downloads the latest update package to Joomla!'s temporary directory
	 *
	 * @return  string  The absolute path to the downloaded update package.
	 */
	public function downloadUpdate()
	{
		// Get the update URL
		$updateInformation = $this->getUpdates();
		$url               = $updateInformation['downloadURL'];

		if (empty($url))
		{
			throw new RuntimeException("No download URL was provided in the update information");
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		if (!$tmp_dest)
		{
			throw new RuntimeException("You must set a non-empty Joomla! temp-directory in Global Configuration before continuing.");
		}

		if (!JFolder::exists($tmp_dest))
		{
			throw new RuntimeException("Joomla!'s temp-directory does not exist. Please set the correct path in Global Configuration before continuing.");
		}

		// Get the target filename
		$filename = $this->component . '.zip';
		$filename = rtrim($tmp_dest, '\\/') . '/' . $filename;

		try
		{
			$downloader = new FOFDownload();
			$data = $downloader->getFromURL($url);
		}
		catch (Exception $e)
		{
			$code =$e->getCode();
			$message =$e->getMessage();
			throw new RuntimeException("An error occurred while trying to download the update package. Double check your Download ID and your server's network settings. The error message was: #$code: $message");
		}

		if (!JFile::write($filename, $data))
		{
			if (!file_put_contents($filename, $data))
			{
				throw new RuntimeException("Joomla!'s temp-directory is not writeable. Please check its permissions or set a different, writeable path in Global Configuration before continuing.");
			}
		}

		return $filename;
	}

	/**
	 * Gets a file name out of a url
	 *
	 * @param   string  $url  URL to get name from
	 *
	 * @return  mixed   String filename or boolean false if failed
	 */
	private function getFilenameFromURL($url)
	{
		if (is_string($url))
		{
			$parts = explode('/', $url);

			return $parts[count($parts) - 1];
		}

		return false;
	}

	/**
	 * Proxy to sendNotificationEmail(). Required since old versions of our software had a sendNotificationEmail method
	 * declared private. If we set the sendNotificationEmail() method public we cause a fatal error.
	 *
	 * @param   string  $version  The new version of our software
	 * @param   string  $email    The email address to send the notification to
	 *
	 * @return  mixed  The result of JMail::send()
	 */
	public function doSendNotificationEmail($version, $email)
	{
		try
		{
			return $this->sendNotificationEmail($version, $email);
		}
		catch (\Exception $e)
		{
			// Joomla! 3.5 is buggy
		}
	}

	/**
	 * Sends an update notification email
	 *
	 * @param   string  $version  The new version of our software
	 * @param   string  $email    The email address to send the notification to
	 *
	 * @return  mixed  The result of JMail::send()
	 */
	private function sendNotificationEmail($version, $email)
	{
		$email_subject	= $this->updateEmailSubject;
		$email_body = $this->updateEmailBody;

		$jconfig  = JFactory::getConfig();
		$sitename = $jconfig->get('sitename');

		$substitutions = array(
				'[VERSION]'			=> $version,
				'[SITENAME]'		=> $sitename,
				'[COMPONENT]'		=> $this->componentDescription,
		);

		$email_subject = str_replace(array_keys($substitutions), array_values($substitutions), $email_subject);
		$email_body    = str_replace(array_keys($substitutions), array_values($substitutions), $email_body);

		$mailer = JFactory::getMailer();

		$mailfrom = $jconfig->get('mailfrom');
		$fromname = $jconfig->get('fromname');

		$mailer->setSender(array( $mailfrom, $fromname ));
		$mailer->addRecipient($email);
		$mailer->setSubject($email_subject);
		$mailer->setBody($email_body);

		return $mailer->Send();
	}
}
