<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.updater.update');
use Joomla\String\StringHelper;

/**
 * Languages Installer Model
 *
 * @since  2.5.7
 */
class InstallerModelLanguages extends JModelList
{
	/**
	 * Language count
	 *
	 * @var     integer
	 * @since   3.7.0
	 */
	private $languageCount;

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
				'name',
				'element',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get the Update Site
	 *
	 * @since   3.7.0
	 *
	 * @return  string  The URL of the Accredited Languagepack Updatesite XML
	 */
	private function getUpdateSite()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('us.location'))
			->from($db->qn('#__extensions', 'e'))
			->where($db->qn('e.type') . ' = ' . $db->q('package'))
			->where($db->qn('e.element') . ' = ' . $db->q('pkg_en-GB'))
			->where($db->qn('e.client_id') . ' = 0')
			->join('LEFT', $db->qn('#__update_sites_extensions', 'use') . ' ON ' . $db->qn('use.extension_id') . ' = ' . $db->qn('e.extension_id'))
			->join('LEFT', $db->qn('#__update_sites', 'us') . ' ON ' . $db->qn('us.update_site_id') . ' = ' . $db->qn('use.update_site_id'));

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.7.0
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			// Load the list items and add the items to the internal cache.
			$this->cache[$store] = $this->getLanguages();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Gets an array of objects from the updatesite.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since   3.0
	 * @throws  RuntimeException
	 */
	protected function getLanguages()
	{
		$updateSite = $this->getUpdateSite();

		// Check whether the updateserver is found
		if (empty($updateSite))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_WARNING_NO_LANGUAGES_UPDATESERVER'), 'warning');

			return;
		}

		$http = new JHttp;

		try
		{
			$response = $http->get($updateSite);
		}
		catch (RuntimeException $e)
		{
			$response = null;
		}

		if ($response === null || $response->code !== 200)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_INSTALLER_MSG_ERROR_CANT_CONNECT_TO_UPDATESERVER', $updateSite), 'error');

			return;
		}

		$updateSiteXML = simplexml_load_string($response->body);
		$languages     = array();
		$search        = strtolower($this->getState('filter.search'));

		foreach ($updateSiteXML->extension as $extension)
		{
			$language = new stdClass;

			foreach ($extension->attributes() as $key => $value)
			{
				$language->$key = (string) $value;
			}

			if ($search)
			{
				if (strpos(strtolower($language->name), $search) === false
					&& strpos(strtolower($language->element), $search) === false)
				{
					continue;
				}
			}

			$languages[$language->name] = $language;
		}

		// Workaround for php 5.3
		$that = $this;

		// Sort the array by value of subarray
		usort(
			$languages,
			function($a, $b) use ($that)
			{
				$ordering = $that->getState('list.ordering');

				if (strtolower($that->getState('list.direction')) === 'asc')
				{
					return StringHelper::strcmp($a->$ordering, $b->$ordering);
				}
				else
				{
					return StringHelper::strcmp($b->$ordering, $a->$ordering);
				}
			}
		);

		// Count the non-paginated list
		$this->languageCount = count($languages);
		$limit               = ($this->getState('list.limit') > 0) ? $this->getState('list.limit') : $this->languageCount;

		return array_slice($languages, $this->getStart(), $limit);
	}

	/**
	 * Returns a record count for the updatesite.
	 *
	 * @param   JDatabaseQuery|string  $query  The query.
	 *
	 * @return  integer  Number of rows for query.
	 *
	 * @since   3.7.0
	 */
	protected function _getListCount($query)
	{
		return $this->languageCount;
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
		$id .= ':' . $this->getState('filter.search');

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
	 * Method to compare two languages in order to sort them.
	 *
	 * @param   object  $lang1  The first language.
	 * @param   object  $lang2  The second language.
	 *
	 * @return  integer
	 *
	 * @since   3.7.0
	 */
	protected function compareLanguages($lang1, $lang2)
	{
		return strcmp($lang1->name, $lang2->name);
	}
}
