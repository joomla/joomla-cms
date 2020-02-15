<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Languages Component Languages Model
 *
 * @since  1.6
 */
class LanguagesModelInstalled extends JModelList
{
	/**
	 * @var object client object
	 * @deprecated 4.0
	 */
	protected $client = null;

	/**
	 * @var object user object
	 */
	protected $user = null;

	/**
	 * @var boolean|JExeption True, if FTP settings should be shown, or an exeption
	 */
	protected $ftp = null;

	/**
	 * @var string option name
	 */
	protected $option = null;

	/**
	 * @var array languages description
	 */
	protected $data = null;

	/**
	 * @var int total number of languages
	 */
	protected $total = null;

	/**
	 * @var int total number of languages installed
	 * @deprecated 4.0
	 */
	protected $langlist = null;

	/**
	 * @var string language path
	 */
	protected $path = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.5
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name',
				'nativeName',
				'language',
				'author',
				'published',
				'version',
				'creationDate',
				'author',
				'authorEmail',
				'extension_id',
				'client_id',
			);
		}

		parent::__construct($config);
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
	 * @since   1.6
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

		// Special case for client id.
		$clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
		$clientId = (!in_array($clientId, array (0, 1))) ? 0 : $clientId;
		$this->setState('client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_languages');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('client_id');
		$id	.= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get the client object.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getClient()
	{
		return JApplicationHelper::getClientInfo($this->getState('client_id', 0));
	}

	/**
	 * Method to get the ftp credentials.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getFtp()
	{
		if (is_null($this->ftp))
		{
			$this->ftp = JClientHelper::setCredentialsFromRequest('ftp');
		}

		return $this->ftp;
	}

	/**
	 * Method to get the option.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getOption()
	{
		$option = $this->getState('option');

		return $option;
	}

	/**
	 * Method to get Languages item data.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getData()
	{
		// Fetch language data if not fetched yet.
		if (is_null($this->data))
		{
			$this->data = array();

			$isCurrentLanguageRtl = JFactory::getLanguage()->isRtl();
			$params               = JComponentHelper::getParams('com_languages');
			$installedLanguages   = JLanguageHelper::getInstalledLanguages(null, true, true, null, null, null);

			// Compute all the languages.
			foreach ($installedLanguages as $clientId => $languages)
			{
				$defaultLanguage = $params->get(JApplicationHelper::getClientInfo($clientId)->name, 'en-GB');

				foreach ($languages as $lang)
				{
					$row               = new stdClass;
					$row->language     = $lang->element;
					$row->name         = $lang->metadata['name'];
					$row->nativeName   = isset($lang->metadata['nativeName']) ? $lang->metadata['nativeName'] : '-';
					$row->client_id    = (int) $lang->client_id;
					$row->extension_id = (int) $lang->extension_id;
					$row->author       = $lang->manifest['author'];
					$row->creationDate = $lang->manifest['creationDate'];
					$row->authorEmail  = $lang->manifest['authorEmail'];
					$row->version      = $lang->manifest['version'];
					$row->published    = $defaultLanguage === $row->language ? 1 : 0;
					$row->checked_out  = 0;

					// Fix wrongly set parentheses in RTL languages
					if ($isCurrentLanguageRtl)
					{
						$row->name       = html_entity_decode($row->name . '&#x200E;', ENT_QUOTES, 'UTF-8');
						$row->nativeName = html_entity_decode($row->nativeName . '&#x200E;', ENT_QUOTES, 'UTF-8');
					}

					$this->data[] = $row;
				}
			}
		}

		$installedLanguages = array_merge($this->data);

		// Process filters.
		$clientId = (int) $this->getState('client_id');
		$search   = $this->getState('filter.search');

		foreach ($installedLanguages as $key => $installedLanguage)
		{
			// Filter by client id.
			if (in_array($clientId, array(0, 1)))
			{
				if ($installedLanguage->client_id !== $clientId)
				{
					unset($installedLanguages[$key]);
					continue;
				}
			}

			// Filter by search term.
			if (!empty($search))
			{
				if (stripos($installedLanguage->name, $search) === false
					&& stripos($installedLanguage->nativeName, $search) === false
					&& stripos($installedLanguage->language, $search) === false)
				{
					unset($installedLanguages[$key]);
					continue;
				}
			}
		}

		// Process ordering.
		$listOrder = $this->getState('list.ordering', 'name');
		$listDirn  = $this->getState('list.direction', 'ASC');
		$installedLanguages = ArrayHelper::sortObjects($installedLanguages, $listOrder, strtolower($listDirn) === 'desc' ? -1 : 1, true, true);

		// Process pagination.
		$limit = (int) $this->getState('list.limit', 25);

		// Sets the total for pagination.
		$this->total = count($installedLanguages);

		if ($limit !== 0)
		{
			$start = (int) $this->getState('list.start', 0);

			return array_slice($installedLanguages, $start, $limit);
		}

		return $installedLanguages;
	}

	/**
	 * Method to get installed languages data.
	 *
	 * @return  string	An SQL query.
	 *
	 * @since   1.6
	 *
	 * @deprecated   4.0
	 */
	protected function getLanguageList()
	{
		// Create a new db object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$client = $this->getState('client_id');
		$type = 'language';

		// Select field element from the extensions table.
		$query->select($this->getState('list.select', 'a.element'))
			->from('#__extensions AS a');

		$type = $db->quote($type);
		$query->where('(a.type = ' . $type . ')')
			->where('state = 0')
			->where('enabled = 1')
			->where('client_id=' . (int) $client);

		// For client_id = 1 do we need to check language table also?
		$db->setQuery($query);

		$this->langlist = $db->loadColumn();

		return $this->langlist;
	}

	/**
	 * Method to get the total number of Languages items.
	 *
	 * @return  integer
	 *
	 * @since   1.6
	 */
	public function getTotal()
	{
		if (is_null($this->total))
		{
			$this->getData();
		}

		return $this->total;
	}

	/**
	 * Method to set the default language.
	 *
	 * @param   integer  $cid  Id of the language to publish.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function publish($cid)
	{
		if ($cid)
		{
			$client = $this->getClient();

			$params = JComponentHelper::getParams('com_languages');
			$params->set($client->name, $cid);

			$table = JTable::getInstance('extension');
			$id    = $table->find(array('element' => 'com_languages'));

			// Load.
			if (!$table->load($id))
			{
				$this->setError($table->getError());

				return false;
			}

			$table->params = (string) $params;

			// Pre-save checks.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Save the changes.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}
		}
		else
		{
			$this->setError(JText::_('COM_LANGUAGES_ERR_NO_LANGUAGE_SELECTED'));

			return false;
		}

		// Clean the cache of com_languages and component cache.
		$this->cleanCache();
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);

		return true;
	}

	/**
	 * Method to get the folders.
	 *
	 * @return  array  Languages folders.
	 *
	 * @since   1.6
	 */
	protected function getFolders()
	{
		if (is_null($this->folders))
		{
			$path = $this->getPath();
			jimport('joomla.filesystem.folder');
			$this->folders = JFolder::folders($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'pdf_fonts', 'overrides'));
		}

		return $this->folders;
	}

	/**
	 * Method to get the path.
	 *
	 * @return  string	The path to the languages folders.
	 *
	 * @since   1.6
	 */
	protected function getPath()
	{
		if (is_null($this->path))
		{
			$client     = $this->getClient();
			$this->path = JLanguageHelper::getLanguagePath($client->path);
		}

		return $this->path;
	}

	/**
	 * Method to compare two languages in order to sort them.
	 *
	 * @param   object  $lang1  The first language.
	 * @param   object  $lang2  The second language.
	 *
	 * @return  integer
	 *
	 * @since   1.6
	 *
	 * @deprecated   4.0
	 */
	protected function compareLanguages($lang1, $lang2)
	{
		return strcmp($lang1->name, $lang2->name);
	}

	/**
	 * Method to switch the administrator language.
	 *
	 * @param   string  $cid  The language tag.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function switchAdminLanguage($cid)
	{
		if ($cid)
		{
			$client = $this->getClient();

			if ($client->name == 'administrator')
			{
				JFactory::getApplication()->setUserState('application.lang', $cid);
			}
		}
		else
		{
			JError::raiseWarning(500, JText::_('COM_LANGUAGES_ERR_NO_LANGUAGE_SELECTED'));

			return false;
		}

		return true;
	}
}
