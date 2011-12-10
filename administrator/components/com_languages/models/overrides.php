<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Languages Overrides Model
 *
 * @package			Joomla.Administrator
 * @subpackage	com_languages
 * @since				2.5
 */
class LanguagesModelOverrides extends JModelList
{
	/**
	 * Constructor
	 *
	 * @param		array	An optional associative array of configuration settings
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->filter_fields = array('key', 'text');
	}

	/**
	 * Retrieves the overrides data
	 *
	 * @param		boolean	True if all overrides shall be returned without considering pagination, defaults to false
	 *
	 * @return	array		Array of objects containing the overrides of the override.ini file
	 *
	 * @since		2.5
	 */
	public function getOverrides($all = false)
	{
		// Get a storage key
		$store = $this->getStoreId();

		// Try to load the data from internal storage
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Parse the override.ini file in oder to get the keys and strings
		$filename = constant('JPATH_'.strtoupper($this->getState('filter.client'))).DS.'language'.DS.'overrides'.DS.$this->getState('filter.language').'.override.ini';
		$strings = LanguagesHelper::parseFile($filename);

		// Consider the odering
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

		// Consider the pagination
		if (!$all && $this->getTotal() > $this->getState('list.limit'))
		{
			$strings = array_slice($strings, $this->getStart(), $this->getState('list.limit'), true);
		}

		// Add the items to the internal cache
		$this->cache[$store] = $strings;

		return $this->cache[$store];
	}

	/**
	 * Method to get the total number of overrides
	 *
	 * @return	int	The total number of overrides
	 *
	 * @since		2.5
	 */
	public function getTotal()
	{
		// Get a storage key
		$store = $this->getStoreId('getTotal');

		// Try to load the data from internal storage
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Add the total to the internal cache
		$this->cache[$store] = count($this->getOverrides(true));

		return $this->cache[$store];
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param		string	An optional ordering field.
	 * @param		string	An optional direction (asc|desc).
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		$language = $this->getUserStateFromRequest('com_languages.overrides.filter.language', 'filter_language', 'en-GB', 'cmd');

		$old_client = $app->getUserState('com_languages.overrides.filter.client', 0);
		$client = $this->getUserStateFromRequest('com_languages.overrides.filter.client', 'filter_client', 0, 'int');
		if ($old_client != $client)
		{
			// If client changes check whether the selected language also
			// exists for the new client. If not, reset it to en-GB
			$reset			= true;
			$path				= constant('JPATH_'.strtoupper($client ? 'administrator' : 'site'));
			$languages	= JLanguageHelper::createLanguageList($language, $path, true, true);
			foreach ($languages as $installed_language)
			{
				if ($installed_language['value'] == $language)
				{
					$reset = false;
					break;
				}
			}
			if ($reset)
			{
				$language = 'en-GB';
			}
		}
		$this->setState('filter.client', $client ? 'administrator' : 'site');
		$this->setState('filter.language', $language);

		// Add filters to the session because they won't be stored there
		// by 'getUserStateFromRequest' if they aren't in the current request
		$app->setUserState('com_languages.overrides.filter.client', $client);
		$app->setUserState('com_languages.overrides.filter.language', $language);

		// List state information
		parent::populateState('key', 'asc');
	}

	/**
	 * Method to delete one or more overrides
	 *
	 * @param		array		Array of keys to delete
	 *
	 * @return	int			Number of successfully deleted overrides, boolean false if an error occured
	 *
	 * @since		2.5
	 */
	public function delete($cids)
	{
		// Check permissions first
		if (!JFactory::getUser()->authorise('core.delete', 'com_languages'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

			return false;
		}

		jimport('joomla.filesystem.file');
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		$app = JFactory::getApplication();

		// Parse the override.ini file in oder to get the keys and strings
		$filename = constant('JPATH_'.strtoupper($this->getState('filter.client'))).DS.'language'.DS.'overrides'.DS.$this->getState('filter.language').'.override.ini';
		$strings = LanguagesHelper::parseFile($filename);

		// Unset strings that shall be deleted
		foreach ($cids as $key)
		{
			if (isset($strings[$key]))
			{
				unset($strings[$key]);
			}
		}

		// Write override.ini file with the left strings
		$registry = new JRegistry();
		$registry->loadObject($strings);

		$filename = constant('JPATH_'.strtoupper($this->getState('filter.client'))).DS.'language'.DS.'overrides'.DS.$this->getState('filter.language').'.override.ini';

		if (!JFile::write($filename, $registry->toString('INI')))
		{
			return false;
		}

		$this->cleanCache();

		return count($cids);
	}
}