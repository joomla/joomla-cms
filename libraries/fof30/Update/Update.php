<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Update;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Model\Model;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Updater\Updater;
use SimpleXMLElement;

/**
 * A helper Model to interact with Joomla!'s extensions update feature
 */
class Update extends Model
{
	/** @var Updater The Joomla! updater object */
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

	/** @var string The component Options key which stores a copy of the Download ID */
	protected $paramsKey = 'update_dlid';

	/**
	 * Public constructor. Initialises the protected members as well. Useful $config keys:
	 * update_component        The component name, e.g. com_foobar
	 * update_version        The default version if the manifest cache is unreadable
	 * update_site            The URL to the component's update XML stream
	 * update_extraquery    The extra query to append to (commercial) components' download URLs
	 * update_sitename        The update site's name (description)
	 * update_paramskey        The component parameters key which holds the license key in J3 (and a copy of it in J4)
	 *
	 * @param   array  $config
	 */
	public function __construct($config = [])
	{
		$container = Container::getInstance('com_FOOBAR');

		if (isset($config['update_container']) && is_object($config['update_container']) && ($config['update_container'] instanceof Container))
		{
			$container = $config['update_container'];
		}

		parent::__construct($container);

		// Get an instance of the updater class
		$this->updater = Updater::getInstance();

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

		// Get the extra query
		if (isset($config['update_paramskey']))
		{
			$this->paramsKey = $config['update_paramskey'];
		}

		// Get the extension type
		$extension = $this->getExtensionObject();

		if (is_object($extension))
		{
			$this->extension_id = $extension->extension_id;

			if (empty($this->version) || ($this->version == 'dev'))
			{
				$data = json_decode($extension->manifest_cache, true);

				if (isset($data['version']))
				{
					$this->version = $data['version'];
				}
			}

		}
	}

	/**
	 * Gets the license key for a paid extension.
	 *
	 * On Joomla! 3 or when $forceLegacy is true we look in the component Options.
	 *
	 * On Joomla! 4 we use the information in the dlid element of the extension's XML manifest to parse the extra_query
	 * fields of all configured update sites of the extension. This is the same thing Joomla does when it tries to
	 * determine the license key of our extension when installing updates. If the extension is missing, it has no
	 * associated update sites, the update sites are missing / rebuilt / disassociated from the extension or the
	 * extra_query of all update site records is empty we parse the $extraQuery set in the constructor, if any. Also
	 * note that on Joomla 4 mode if the extension does not exist, does not have a manifest or does not have a valid
	 * dlid element in its manifest we will end up returning an empty string, just like Joomla! itself would have done
	 * when installing updates.
	 *
	 * @param   bool  $forceLegacy  Should I always retrieve the legacy license key, even in J4?
	 *
	 * @return  string
	 */
	public function getLicenseKey($forceLegacy = false)
	{
		$legacyParamsKey = $this->getLegacyParamsKey();

		// Joomla 3 (Legacy): Download ID stored in the component options
		if ($forceLegacy || !version_compare(JVERSION, '3.999.999', 'gt'))
		{
			return $this->container->params->get($legacyParamsKey, '');
		}

		// Joomla! 4. We need to parse the extra_query of the update sites to get the correct Download ID.
		$updateSites = $this->getUpdateSites();
		$extra_query = array_reduce($updateSites, function ($extra_query, $updateSite) {
			if (!empty($extra_query))
			{
				return $extra_query;
			}

			return $updateSite['extra_query'];
		}, '');

		// Fall back to legacy extra query
		if (empty($extra_query))
		{
			$extra_query = $this->extraQuery;
		}

		// Return the parsed results.
		return $this->getLicenseKeyFromExtraQuery($extra_query);
	}

	/**
	 * Get the contents of all the update sites of the configured extension
	 *
	 * @return  array
	 */
	public function getUpdateSites()
	{
		$updateSiteIDs = $this->getUpdateSiteIds();
		$db            = $this->container->db;
		$query         = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__update_sites'))
			->where($db->qn('update_site_id') . ' IN (' . implode(', ', $updateSiteIDs) . ')');

		try
		{
			$db->setQuery($query);

			$ret = $db->loadAssocList('update_site_id');
		}
		catch (Exception $e)
		{
			$ret = null;
		}

		return empty($ret) ? [] : $ret;
	}

	public function setLicenseKey($licenseKey)
	{
		$legacyParamsKey = $this->getLegacyParamsKey();

		// Sanitize and validate the license key. If it's not valid we set an empty license key.
		$licenseKey = $this->sanitizeLicenseKey($licenseKey);

		if (!$this->isValidLicenseKey($licenseKey))
		{
			$licenseKey = '';
		}

		// Update $this->extraQuery.
		$this->extraQuery = $this->getExtraQueryString($licenseKey);

		// Save the license key in the component options ($legacyParamsKey)
		$this->container->params->set($legacyParamsKey, $licenseKey);
		$this->container->params->save();

		// Apply the new extra_query to the update site
		$this->refreshUpdateSite();
	}

	/**
	 * Copies a Joomla 3 license key from the Options storage to Joomla 4 download key storage (the extra_query column
	 * of the #__update_sites table).
	 *
	 * This method does nothing on Joomla 3.
	 *
	 * @return  void
	 */
	public function upgradeLicenseKey()
	{
		// Only applies to Joomla! 4
		if (!version_compare(JVERSION, '3.999.999', 'gt'))
		{
			return;
		}

		// Make sure we DO have a legacy license key
		$legacyKey = $this->getLicenseKey(true);

		if (empty($legacyKey))
		{
			return;
		}

		// Make sure we DO NOT have a J4 key. If we do, the J4 key wins and gets backported to legacy storage.
		$licenseKey = $this->getLicenseKey(false);

		if (!empty($licenseKey))
		{
			$this->backportLicenseKey();

			return;
		}

		// Save the legacy key as non-legacy. This updates the #__update_sites record, applying the license key.
		$this->setLicenseKey($legacyKey);
	}

	/**
	 * Copies a Joomla 4 license key from the download key storage (the extra_query column of the #__update_sites table)
	 * to the legacy Options storage.
	 *
	 * This method does nothing on Joomla 3.
	 *
	 * @return  void
	 */
	public function backportLicenseKey()
	{
		$legacyParamsKey = $this->getLegacyParamsKey();

		// Only applies to Joomla! 4
		if (!version_compare(JVERSION, '3.999.999', 'gt'))
		{
			return;
		}

		// Make sure we DO have a J4 key
		$licenseKey = $this->getLicenseKey(false);

		if (empty($licenseKey))
		{
			return;
		}

		// Make sure that the legacy key is NOT the same as the J4 key
		$legacyKey = $this->getLicenseKey(true);

		if ($legacyKey == $licenseKey)
		{
			return;
		}

		// Save the license key to the legacy storage (component options)
		$this->container->params->set($legacyParamsKey, $licenseKey);
		$this->container->params->save();
	}

	/**
	 * Get an extra query string based on the dlid element of the XML manifest file of the extension.
	 *
	 * If the extension does not exist, the manifest does not exist or it does not have a dlid element we fall back to
	 * the legacy implementation of extra_query (getExtraQueryStringLegacy)
	 *
	 * @param   string  $licenseKey
	 *
	 * @return  string
	 */
	public function getExtraQueryString($licenseKey)
	{
		// Make sure the (sanitized) license key is valid. Otherwise we return an empty string.
		$licenseKey = $this->sanitizeLicenseKey($licenseKey);

		if (!$this->isValidLicenseKey($licenseKey))
		{
			return '';
		}

		// Get a fallback extra query using the legacy method
		$fallbackExtraQuery = $this->getExtraQueryStringLegacy($licenseKey);

		// Get the extension XML manifest. If the extension or the manifest don't exist use the fallback extra_query.
		$extension = $this->getExtensionObject();

		if (!$extension)
		{
			return $fallbackExtraQuery;
		}

		$installXmlFile = $this->getManifestXML(
			$extension->element,
			$extension->type,
			(int) $extension->client_id,
			$extension->folder
		);

		if (!$installXmlFile)
		{
			return $fallbackExtraQuery;
		}

		// If the manifest does not have the dlid element return the fallback extra_query.
		if (!isset($installXmlFile->dlid))
		{
			return $fallbackExtraQuery;
		}

		$prefix = (string) $installXmlFile->dlid['prefix'];
		$suffix = (string) $installXmlFile->dlid['suffix'];

		return $prefix . $this->sanitizeLicenseKey($licenseKey) . $suffix;
	}

	/**
	 * Retrieves the update information of the component, returning an array with the following keys:
	 *
	 * hasUpdate  True if an update is available
	 * version    The version of the available update
	 * infoURL    The URL to the download page of the update
	 *
	 * @param   bool  $force  Set to true if you want to forcibly reload the update information
	 *
	 * @return  array  See the method description for more information
	 */
	public function getUpdates($force = false)
	{
		$db = $this->container->db;

		// Default response (no update)
		$updateResponse = [
			'hasUpdate' => false,
			'version'   => '',
			'infoURL'   => '',
		];

		if (empty($this->extension_id))
		{
			return $updateResponse;
		}

		// If we had to update the version number stored in the database then we should force reload the updates
		if ($this->updatedCachedVersionNumber())
		{
			$force = true;
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
				->where($db->qn('update_site_id') . ' IN (' . implode(', ', $updateSiteIds) . ')');
			$db->setQuery($query);
			$db->execute();

			// Remove cached component update info from #__updates
			$query = $db->getQuery(true)
				->delete($db->qn('#__updates'))
				->where($db->qn('update_site_id') . ' IN (' . implode(', ', $updateSiteIds) . ')');
			$db->setQuery($query);
			$db->execute();
		}

		// Use the update cache timeout specified in com_installer
		$comInstallerParams = ComponentHelper::getParams('com_installer', false);
		$timeout            = 3600 * $comInstallerParams->get('cachetimeout', '6');

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
			$updateResponse = [
				'hasUpdate' => true,
				'version'   => $updateRecord->version,
				'infoURL'   => $updateRecord->infourl,
			];
		}

		return $updateResponse;
	}

	/**
	 * Gets the update site Ids for our extension.
	 *
	 * @return    array    An array of IDs
	 */
	public function getUpdateSiteIds()
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($this->extension_id));
		$db->setQuery($query);

		try
		{
			$ret = $db->loadColumn(0);
		}
		catch (Exception $e)
		{
			$ret = null;
		}

		return is_array($ret) ? $ret : [];
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
	 * @param   string  $version
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
		$update_site = [
			'name'                 => $this->updateSiteName,
			'type'                 => 'extension',
			'location'             => $this->updateSite,
			'enabled'              => 1,
			'last_check_timestamp' => 0,
			'extra_query'          => $this->extraQuery,
		];

		// Get a reference to the db driver
		$db = $this->container->db;

		// Get the #__update_sites columns
		$columns = $db->getTableColumns('#__update_sites', true);

		if (version_compare(JVERSION, '3.0.0', 'lt') || !array_key_exists('extra_query', $columns))
		{
			unset($update_site['extra_query']);
		}

		// Get the update sites for our extension
		$updateSiteIds = $this->getUpdateSiteIds();

		if (empty($updateSiteIds))
		{
			$updateSiteIds = [];
		}

		/** @var boolean $needNewUpdateSite Do I need to create a new update site? */
		$needNewUpdateSite = true;

		/** @var int[] $deleteOldSites Old Site IDs to delete */
		$deleteOldSites = [];

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
				$mustUpdate        = false;

				// Is it enabled? If not, enable it.
				if (!$aSite->enabled)
				{
					$mustUpdate     = true;
					$aSite->enabled = 1;
				}

				// Do we have the extra_query property (J 3.2+) and does it match?
				if (property_exists($aSite, 'extra_query') && isset($update_site['extra_query'])
					&& ($aSite->extra_query != $update_site['extra_query']))
				{
					$mustUpdate         = true;
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
				$obsoleteIDsQuoted = array_map([$db, 'quote'], $deleteOldSites);

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
			catch (Exception $e)
			{
				// Do nothing on failure
				return;
			}

		}

		// Do we still need to create a new update site?
		if ($needNewUpdateSite)
		{
			// No update sites defined. Create a new one.
			$newSite = (object) $update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id                  = $db->insertid();
			$updateSiteExtension = (object) [
				'update_site_id' => $id,
				'extension_id'   => $this->extension_id,
			];
			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
	}

	/**
	 * Removes any update sites which go by the same name or the same location as our update site but do not match the
	 * extension ID.
	 */
	public function removeObsoleteUpdateSites()
	{
		$db = $this->container->db;

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
			$updateSitesQuoted = array_map([$db, 'quote'], $updateSiteIDs);
			$query->where($db->qn('update_site_id') . ' NOT IN (' . implode(',', $updateSitesQuoted) . ')');
		}

		try
		{
			$ids = $db->setQuery($query)->loadColumn();

			if (!empty($ids))
			{
				$obsoleteIDsQuoted = array_map([$db, 'quote'], $ids);

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
		catch (Exception $e)
		{
			// Do nothing on failure
			return;
		}
	}

	/**
	 * Makes sure that the version number cached in the #__extensions table is consistent with the version number set in
	 * this model.
	 *
	 * @return  bool  True if we updated the version number cached in the #__extensions table.
	 *
	 * @since   3.1.2
	 */
	public function updatedCachedVersionNumber()
	{
		$extension = $this->getExtensionObject();

		if (!is_object($extension))
		{
			return false;
		}

		$data       = json_decode($extension->manifest_cache, true);
		$mustUpdate = true;

		if (isset($data['version']))
		{
			$mustUpdate = $this->version != $data['version'];
		}

		if (!$mustUpdate)
		{
			return false;
		}

		// The cached version is wrong; let's update it
		$data['version']           = $this->version;
		$extension->manifest_cache = json_encode($data);
		$db                        = $this->container->db;

		return $db->updateObject('#__extensions', $extension, ['extension_id']);
	}

	/**
	 * Returns an object with the #__extensions table record for the current extension.
	 *
	 * @return  mixed
	 */
	public function getExtensionObject()
	{
		[$extensionPrefix, $extensionName] = explode('_', $this->component);

		switch ($extensionPrefix)
		{
			default:
			case 'com':
				$type = 'component';
				$name = $this->component;
				break;

			case 'pkg':
				$type = 'package';
				$name = $this->component;
				break;
		}

		// Find the extension ID
		$db    = $this->container->db;
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q($type))
			->where($db->qn('element') . ' = ' . $db->q($name));

		try
		{
			$db->setQuery($query);
			$extension = $db->loadObject();
		}
		catch (Exception $e)
		{
			return null;
		}

		return $extension;
	}

	/**
	 * Is the provided string a valid license key?
	 *
	 * YOU SHOULD OVERRIDE THIS METHOD. The default implementation checks for valid Download IDs in the format used by
	 * Akeeba software.
	 *
	 * @param   string  $licenseKey
	 *
	 * @return  bool
	 */
	public function isValidLicenseKey($licenseKey)
	{
		return preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $licenseKey) === 1;
	}

	/**
	 * Sanitizes the license key.
	 *
	 * YOU SHOULD OVERRIDE THIS METHOD. The default implementation returns a lowercase string with all characters except
	 * letters, numbers and colons removed.
	 *
	 * @param   string  $licenseKey
	 *
	 * @return  string  The sanitized license key
	 */
	public function sanitizeLicenseKey($licenseKey)
	{
		return strtolower(preg_replace("/[^a-zA-Z0-9:]/", "", $licenseKey));
	}

	/**
	 * Returns the component Options key which holds a copy of the license key
	 *
	 * @return  string
	 */
	protected function getLegacyParamsKey()
	{
		if (!empty($this->paramsKey))
		{
			return $this->paramsKey;
		}

		$this->paramsKey = 'update_dlid';

		return $this->paramsKey;
	}

	/**
	 * Extract the download ID from an extra_query based on the prefix and suffix information stored in the dlid element
	 * of the extension's XML manifest file.
	 *
	 * @param   string  $extra_query
	 *
	 * @return string
	 */
	protected function getLicenseKeyFromExtraQuery($extra_query)
	{
		$extra_query = trim($extra_query);

		if (empty($extra_query))
		{
			return '';
		}

		// Get the extension XML manifest. If the extension or the manifest don't exist return an empty string.
		$extension = $this->getExtensionObject();

		if (!$extension)
		{
			return '';
		}

		$installXmlFile = $this->getManifestXML(
			$extension->element,
			$extension->type,
			(int) $extension->client_id,
			$extension->folder
		);

		if (!$installXmlFile)
		{
			return '';
		}

		// If the manifest does not have a dlid element return an empty string.
		if (!isset($installXmlFile->dlid))
		{
			return '';
		}

		// Naive parsing of the extra_query, the same way Joomla does.
		$prefix     = (string) $installXmlFile->dlid['prefix'];
		$suffix     = (string) $installXmlFile->dlid['suffix'];
		$licenseKey = substr($extra_query, strlen($prefix));

		if ($licenseKey === false)
		{
			return '';
		}

		if ($suffix)
		{
			$licenseKey = substr($licenseKey, 0, -strlen($suffix));
		}

		return ($licenseKey === false) ? '' : $licenseKey;
	}

	/**
	 * Get a legacy extra query string. Do NOT call this directly. Call getExtraQueryString() instead.
	 *
	 * YOU SHOULD OVERRIDE THIS METHOD. This returns dlid=SANITIZED_LICENSE_KEY which is what Akeeba Release System,
	 * used to deliver all Akeeba extensions, expects.
	 *
	 * @param   string  $licenseKey  The license key
	 *
	 * @return  string  The extra_query string to append to a downlaod URL to implement the license key
	 */
	protected function getExtraQueryStringLegacy($licenseKey)
	{
		if (empty($licenseKey) || !$this->isValidLicenseKey($licenseKey))
		{
			return '';
		}

		return 'dlid=' . $this->sanitizeLicenseKey($licenseKey);
	}

	/**
	 * Get the manifest XML file of a given extension.
	 *
	 * @param   string   $element    element of an extension
	 * @param   string   $type       type of an extension
	 * @param   integer  $client_id  client_id of an extension
	 * @param   string   $folder     folder of an extension
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getManifestXML($element, $type, $client_id = 1, $folder = null)
	{
		$path = $client_id ? JPATH_ADMINISTRATOR : JPATH_ROOT;

		switch ($type)
		{
			case 'component':
				$path .= '/components/' . $element . '/' . substr($element, 4) . '.xml';
				break;
			case 'plugin':
				$path .= '/plugins/' . $folder . '/' . $element . '/' . $element . '.xml';
				break;
			case 'module':
				$path .= '/modules/' . $element . '/' . $element . '.xml';
				break;
			case 'template':
				$path .= '/templates/' . $element . '/templateDetails.xml';
				break;
			case 'library':
				$path = JPATH_ADMINISTRATOR . '/manifests/libraries/' . $element . '.xml';
				break;
			case 'file':
				$path = JPATH_ADMINISTRATOR . '/manifests/files/' . $element . '.xml';
				break;
			case 'package':
				$path = JPATH_ADMINISTRATOR . '/manifests/packages/' . $element . '.xml';
		}

		return simplexml_load_file($path);
	}
}
