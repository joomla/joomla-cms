<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.updater.update');

/**
 * Languages Installer Model
 *
 * @since  2.5.7
 */
class InstallerModelLanguages extends JModelList
{
	/**
	 * Extension ID of the en-GB language pack.
	 *
	 * @var     integer
	 * @since   3.4
	 */
	private $enGbExtensionId = 0;

	/**
	 * Upate Site ID of the en-GB language pack.
	 *
	 * @var     integer
	 * @since   3.4
	 */
	private $updateSiteId = 0;

	/**
	 * Constructor override, defines a whitelist of column filters.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.5.7
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'update_id',
				'name',
				'element',
			);
		}

		parent::__construct($config);

		// Get the extension_id of the en-GB package.
		$db        = $this->getDbo();
		$extQuery  = $db->getQuery(true);

		$extQuery->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('package'))
			->where($db->quoteName('element') . ' = ' . $db->quote('pkg_en-GB'))
			->where($db->quoteName('client_id') . ' = 0');

		$db->setQuery($extQuery);

		$extId = (int) $db->loadResult();

		// Get the update_site_id for the en-GB package if extension_id found before.
		if ($extId)
		{
			$this->enGbExtensionId = $extId;

			$siteQuery = $db->getQuery(true);

			$siteQuery->select($db->quoteName('update_site_id'))
				->from($db->quoteName('#__update_sites_extensions'))
				->where($db->quoteName('extension_id') . ' = ' . $extId);

			$db->setQuery($siteQuery);

			$siteId = (int) $db->loadResult();

			if ($siteId)
			{
				$this->updateSiteId = $siteId;
			}
		}
	}

	/**
	 * Method to get the available languages database query.
	 *
	 * @return  JDatabaseQuery  The database query
	 *
	 * @since   2.5.7
	 */
	protected function _getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the updates table.
		$query->select($db->quoteName(array('update_id', 'name', 'element', 'version', 'detailsurl', 'type')))
			->from($db->quoteName('#__updates'));

		/*
		 * This where clause will limit to language updates only.
		 * If no update site exists, set the where clause so
		 * no available languages will be found later with the
		 * query returned by this function here.
		 */
		if ($this->updateSiteId)
		{
			$query->where($db->quoteName('update_site_id') . ' = ' . $this->updateSiteId);
		}
		else
		{
			$query->where($db->quoteName('update_site_id') . ' = -1');
		}

		// This where clause will avoid to list languages already installed.
		$query->where($db->quoteName('extension_id') . ' = 0');

		// Filter by search in title and language tag.
		if ($search = $this->getState('filter.search'))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(LOWER(name) LIKE ' . strtolower($search) . ' OR LOWER(element) LIKE ' . strtolower($search) . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'name')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.5.7
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   list order
	 * @param   string  $direction  direction in the list
	 *
	 * @return  void
	 *
	 * @since   2.5.7
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

		$this->setState('extension_message', JFactory::getApplication()->getUserState('com_installer.extension_message'));

		parent::populateState($ordering, $direction);
	}

	/**
	 * Enable languages update server
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	protected function enableUpdateSite()
	{
		// If no update site, return false.
		if (!$this->updateSiteId)
		{
			return false;
		}

		// Try to enable the update site, return false if some RuntimeException
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set('enabled = 1')
			->where('update_site_id = ' . $this->updateSiteId);

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to find available languages in the Accredited Languages Update Site.
	 *
	 * @param   int  $cache_timeout  time before refreshing the cached updates
	 *
	 * @return  bool
	 *
	 * @since   2.5.7
	 */
	public function findLanguages($cache_timeout = 0)
	{
		if (!$this->enableUpdateSite())
		{
			return false;
		}

		if (!$this->enGbExtensionId)
		{
			return false;
		}

		$updater = JUpdater::getInstance();

		/*
		 * The following function call uses the extension_id of the en-GB package.
		 * In #__update_sites_extensions you should have this extension_id linked
		 * to the Accredited Translations Repo.
		 */
		$updater->findUpdates(array($this->enGbExtensionId), $cache_timeout);

		return true;
	}

	/**
	 * Install languages in the system.
	 *
	 * @param   array  $lids  array of language ids selected in the list
	 *
	 * @return  bool
	 *
	 * @since   2.5.7
	 */
	public function install($lids)
	{
		$app = JFactory::getApplication();

		// Loop through every selected language
		foreach ($lids as $id)
		{
			$installer = new JInstaller;

			// Loads the update database object that represents the language.
			$language = JTable::getInstance('update');
			$language->load($id);

			// Get the url to the XML manifest file of the selected language.
			$remote_manifest = $this->_getLanguageManifest($id);

			if (!$remote_manifest)
			{
				// Could not find the url, the information in the update server may be corrupt.
				$message  = JText::sprintf('COM_INSTALLER_MSG_LANGUAGES_CANT_FIND_REMOTE_MANIFEST', $language->name);
				$message .= ' ' . JText::_('COM_INSTALLER_MSG_LANGUAGES_TRY_LATER');
				$app->enqueueMessage($message, 'warning');

				continue;
			}

			// Based on the language XML manifest get the url of the package to download.
			$package_url = $this->_getPackageUrl($remote_manifest);

			if (!$package_url)
			{
				// Could not find the url , maybe the url is wrong in the update server, or there is not internet access
				$message  = JText::sprintf('COM_INSTALLER_MSG_LANGUAGES_CANT_FIND_REMOTE_PACKAGE', $language->name);
				$message .= ' ' . JText::_('COM_INSTALLER_MSG_LANGUAGES_TRY_LATER');
				$app->enqueueMessage($message, 'warning');

				continue;
			}

			// Download the package to the tmp folder.
			$package = $this->_downloadPackage($package_url);

			// Install the package
			if (!$installer->install($package['dir']))
			{
				// There was an error installing the package.
				$message  = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', $language->name);
				$message .= ' ' . JText::_('COM_INSTALLER_MSG_LANGUAGES_TRY_LATER');
				$app->enqueueMessage($message, 'error');

				continue;
			}

			// Package installed successfully.
			$app->enqueueMessage(JText::sprintf('COM_INSTALLER_INSTALL_LANGUAGE_SUCCESS', $language->name));

			// Cleanup the install files in tmp folder.
			if (!is_file($package['packagefile']))
			{
				$package['packagefile'] = JFactory::getConfig()->get('tmp_path') . '/' . $package['packagefile'];
			}

			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			// Delete the installed language from the list.
			$language->delete($id);
		}
	}

	/**
	 * Gets the manifest file of a selected language from a the language list in an update server.
	 *
	 * @param   int  $uid  the id of the language in the #__updates table
	 *
	 * @return  string
	 *
	 * @since   2.5.7
	 */
	protected function _getLanguageManifest($uid)
	{
		$instance = JTable::getInstance('update');
		$instance->load($uid);

		return $instance->detailsurl;
	}

	/**
	 * Finds the url of the package to download.
	 *
	 * @param   string  $remote_manifest  url to the manifest XML file of the remote package
	 *
	 * @return  string|bool
	 *
	 * @since   2.5.7
	 */
	protected function _getPackageUrl($remote_manifest)
	{
		$update = new JUpdate;
		$update->loadFromXml($remote_manifest);
		$downloadUrlElement = $update->get('downloadurl', false);

		if ($downloadUrlElement === false)
		{
			return false;
		}

		return trim($downloadUrlElement->_data);
	}

	/**
	 * Download a language package from a URL and unpack it in the tmp folder.
	 *
	 * @param   string  $url  hola
	 *
	 * @return  array|bool  Package details or false on failure
	 *
	 * @since   2.5.7
	 */
	protected function _downloadPackage($url)
	{
		// Download the package from the given URL.
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));

			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file.
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		return $package;
	}
}
