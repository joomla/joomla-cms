<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.updater.update');

/**
 * Languages Installer Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       2.5.7
 */
class InstallerModelLanguages extends JModelList
{
	/**
	 * Constructor override, defines a white list of column filters.
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
				'update_id', 'update_id',
				'name', 'name',
			);
		}

		parent::__construct($config);
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
		$db   = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the updates table
		$query->select('update_id, name, version, detailsurl, type')

			->from('#__updates');

		// This Where clause will avoid to list languages already installed.
		$query->where('extension_id = 0');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(name LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$listOrder = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$query->order($db->escape($listOrder) . ' ' . $db->escape($orderDirn));

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
		$app = JFactory::getApplication();

		$value = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));

		parent::populateState($ordering, $direction);
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
		$updater = JUpdater::getInstance();

		/*
		 * The following function uses extension_id 600, that is the english language extension id.
		 * In #__update_sites_extensions you should have 600 linked to the Accredited Translations Repo
		 */
		$updater->findUpdates(array(600), $cache_timeout);

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
		$app       = JFactory::getApplication();
		$installer = JInstaller::getInstance();

		// Loop through every selected language
		foreach ($lids as $id)
		{
			// Loads the update database object that represents the language
			$language = JTable::getInstance('update');
			$language->load($id);

			// Get the url to the XML manifest file of the selected language
			$remote_manifest = $this->_getLanguageManifest($id);
			if (!$remote_manifest)
			{
				// Could not find the url, the information in the update server may be corrupt
				$message  = JText::sprintf('COM_INSTALLER_MSG_LANGUAGES_CANT_FIND_REMOTE_MANIFEST', $language->name);
				$message .= ' ' . JText::_('COM_INSTALLER_MSG_LANGUAGES_TRY_LATER');
				$app->enqueueMessage($message);
				continue;
			}

			// Based on the language XML manifest get the url of the package to download
			$package_url = $this->_getPackageUrl($remote_manifest);
			if (!$package_url)
			{
				// Could not find the url , maybe the url is wrong in the update server, or there is not internet access
				$message  = JText::sprintf('COM_INSTALLER_MSG_LANGUAGES_CANT_FIND_REMOTE_PACKAGE', $language->name);
				$message .= ' ' . JText::_('COM_INSTALLER_MSG_LANGUAGES_TRY_LATER');
				$app->enqueueMessage($message);
				continue;
			}

			// Download the package to the tmp folder
			$package = $this->_downloadPackage($package_url);

			// Install the package
			if (!$installer->install($package['dir']))
			{
				// There was an error installing the package
				$message  = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', $language->name);
				$message .= ' ' . JText::_('COM_INSTALLER_MSG_LANGUAGES_TRY_LATER');
				$app->enqueueMessage($message);
				continue;
			}

			// Package installed successfully
			$app->enqueueMessage(JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', $language->name));

			// Cleanup the install files in tmp folder
			if (!is_file($package['packagefile']))
			{
				$config = JFactory::getConfig();
				$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
			}
			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			// Delete the installed language from the list
			$language->delete($id);
		}

	}

	/**
	 * Gets the manifest file of a selected language from a the language list in a update server.
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
	protected function _getPackageUrl( $remote_manifest )
	{
		$update = new JUpdate;
		$update->loadFromXML($remote_manifest);
		$package_url = trim($update->get('downloadurl', false)->_data);

		return $package_url;
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
		// Download the package from the given URL
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));
			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		return $package;
	}
}
