<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Languages Overrides Model
 *
 * @since  2.5
 */
class LanguagesModelOverrides extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->filter_fields = array('key', 'text');
	}

	/**
	 * Retrieves the overrides data
	 *
	 * @param   boolean  $all  True if all overrides shall be returned without considering pagination, defaults to false
	 *
	 * @return  array  Array of objects containing the overrides of the override.ini file
	 *
	 * @since   2.5
	 */
	public function getOverrides($all = false)
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$client = strtoupper($this->getState('filter.client'));

		// Parse the override.ini file in order to get the keys and strings.
		$fileName = constant('JPATH_' . $client) . '/language/overrides/' . $this->getState('filter.language') . '.override.ini';
		$strings  = JLanguageHelper::parseIniFile($fileName);

		// Delete the override.ini file if empty.
		if (file_exists($fileName) && $strings === array())
		{
			JFile::delete($fileName);
		}

		// Filter the loaded strings according to the search box.
		$search = $this->getState('filter.search');

		if ($search != '')
		{
			$search = preg_quote($search, '~');
			$matchvals = preg_grep('~' . $search . '~i', $strings);
			$matchkeys = array_intersect_key($strings, array_flip(preg_grep('~' . $search . '~i',  array_keys($strings))));
			$strings = array_merge($matchvals, $matchkeys);
		}

		// Consider the ordering
		if ($this->getState('list.ordering') == 'text')
		{
			if (strtoupper($this->getState('list.direction')) == 'DESC')
			{
				arsort($strings);
			}
			else
			{
				asort($strings);
			}
		}
		else
		{
			if (strtoupper($this->getState('list.direction')) == 'DESC')
			{
				krsort($strings);
			}
			else
			{
				ksort($strings);
			}
		}

		// Consider the pagination.
		if (!$all && $this->getState('list.limit') && $this->getTotal() > $this->getState('list.limit'))
		{
			$strings = array_slice($strings, $this->getStart(), $this->getState('list.limit'), true);
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $strings;

		return $this->cache[$store];
	}

	/**
	 * Method to get the total number of overrides.
	 *
	 * @return  integer  The total number of overrides.
	 *
	 * @since   2.5
	 */
	public function getTotal()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Add the total to the internal cache.
		$this->cache[$store] = count($this->getOverrides(true));

		return $this->cache[$store];
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState($ordering = 'key', $direction = 'asc')
	{
		// We call populate state first so that we can then set the filter.client and filter.language properties in afterwards
		parent::populateState($ordering, $direction);

		$app = JFactory::getApplication();

		$language_client = $this->getUserStateFromRequest('com_languages.overrides.language_client', 'language_client', '', 'cmd');
		$client          = substr($language_client, -1);
		$language        = substr($language_client, 0, -1);

		// Sets the search filter.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$this->setState('language_client', $language . $client);
		$this->setState('filter.client', $client ? 'administrator' : 'site');
		$this->setState('filter.language', $language);

		// Add the 'language_client' value to the session to display a message if none selected
		$app->setUserState('com_languages.overrides.language_client', $language . $client);

		// Add filters to the session because they won't be stored there by 'getUserStateFromRequest' if they aren't in the current request.
		$app->setUserState('com_languages.overrides.filter.client', $client);
		$app->setUserState('com_languages.overrides.filter.language', $language);
	}

	/**
	 * Method to delete one or more overrides.
	 *
	 * @param   array  $cids  Array of keys to delete.
	 *
	 * @return  integer  Number of successfully deleted overrides, boolean false if an error occurred.
	 *
	 * @since   2.5
	 */
	public function delete($cids)
	{
		// Check permissions first.
		if (!JFactory::getUser()->authorise('core.delete', 'com_languages'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

			return false;
		}

		jimport('joomla.filesystem.file');

		$filterclient = JFactory::getApplication()->getUserState('com_languages.overrides.filter.client');
		$client = $filterclient == 0 ? 'SITE' : 'ADMINISTRATOR';

		// Parse the override.ini file in oder to get the keys and strings.
		$fileName = constant('JPATH_' . $client) . '/language/overrides/' . $this->getState('filter.language') . '.override.ini';
		$strings  = JLanguageHelper::parseIniFile($fileName);

		// Unset strings that shall be deleted
		foreach ($cids as $key)
		{
			if (isset($strings[$key]))
			{
				unset($strings[$key]);
			}
		}

		// Write override.ini file with the strings.
		if (JLanguageHelper::saveToIniFile($fileName, $strings) === false)
		{
			return false;
		}

		$this->cleanCache();

		return count($cids);
	}

	/**
	 * Removes all of the cached strings from the table.
	 *
	 * @return  boolean  result of operation
	 *
	 * @since   3.4.2
	 */
	public function purge()
	{
		$db = JFactory::getDbo();

		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		try
		{
			$db->truncateTable('#__overrider');
		}
		catch (RuntimeException $e)
		{
			return $e;
		}

		JFactory::getApplication()->enqueueMessage(JText::_('COM_LANGUAGES_VIEW_OVERRIDES_PURGE_SUCCESS'));
	}
}
