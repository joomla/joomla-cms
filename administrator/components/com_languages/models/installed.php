<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Languages Component Languages Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesModelInstalled extends JModelList
{
	/**
	 * @var object client object
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
	 * @var int total number pf languages
	 */
	protected $total = null;

	/**
	 * @var int total number pf languages installed
	 */
	protected $langlist = null;

	/**
	 * @var string language path
	 */
	protected $path = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$clientId = JRequest::getInt('client');
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_languages');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.name', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.client_id');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get the client object
	 *
	 * @return	object
	 * @since	1.6
	 */
	public function &getClient()
	{
		if (is_null($this->client)) {
			$this->client = JApplicationHelper::getClientInfo($this->getState('filter.client_id', 0));
		}

		return $this->client;
	}

	/**
	 * Method to get the ftp credentials
	 *
	 * @return	object
	 * @since	1.6
	 */
	public function &getFtp()
	{
		if (is_null($this->ftp)) {
			$this->ftp = JClientHelper::setCredentialsFromRequest('ftp');
		}

		return $this->ftp;
	}

	/**
	 * Method to get the option
	 *
	 * @return	object
	 * @since	1.6
	 */
	public function &getOption()
	{
		$option = $this->getState('option');

		return $option;
	}

	/**
	 * Method to get Languages item data
	 *
	 * @return	array
	 * @since	1.6
	 */
	public function &getData()
	{
		if (is_null($this->data)) {

			// Get information
			$path		= $this->getPath();
			$client		= $this->getClient();
			$langlist   = $this->getLanguageList();

			// Compute all the languages
			$data	= array ();

			foreach($langlist as $lang){
				$file = $path . '/' . $lang . '/' . $lang.'.xml';
				$info = JApplicationHelper::parseXMLLangMetaFile($file);
				$row = new JObject();
				$row->language = $lang;

				if (!is_array($info)) {
					continue;
				}

				foreach($info as $key => $value)
				{
					$row->$key = $value;
				}

				// if current than set published
				$params = JComponentHelper::getParams('com_languages');
				if ($params->get($client->name, 'en-GB') == $row->language) {
					$row->published	= 1;
				}
				else {
					$row->published = 0;
				}

				$row->checked_out = 0;
				$data[] = $row;
			}
			usort($data, array($this, 'compareLanguages'));

			// Prepare data
			$limit = $this->getState('list.limit');
			$start = $this->getState('list.start');
			$total = $this->getTotal();

			if ($limit == 0) {
				$start = 0;
				$end = $total;
			}
			else {
				if ($start > $total) {
					$start = $total - $total % $limit;
				}
				$end = $start + $limit;

				if ($end > $total) {
					$end = $total;
				}
			}

			// Compute the displayed languages
			$this->data	= array();
			for ($i = $start;$i < $end;$i++)
			{
				$this->data[] = & $data[$i];
			}
		}

		return $this->data;
	}

	/**
	 * Method to get installed languages data.
	 *
	 * @return	string	An SQL query
	 * @since	1.6
	 */
	protected function getLanguageList()
	{
		// Create a new db object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$client = $this->getState('filter.client_id');
		$type = "language";
		// Select field element from the extensions table.
		$query->select($this->getState('list.select', 'a.element'));
		$query->from('#__extensions AS a');

		$type = $db->Quote($type);
		$query->where('(a.type = '.$type.')');

		$query->where('state = 0');
		$query->where('enabled = 1');

		$query->where('client_id=' . intval($client));

		// for client_id = 1 do we need to check language table also ?
		$db->setQuery($query);

		$this->langlist = $db->loadColumn();

		return $this->langlist;
	}

	/**
	 * Method to get the total number of Languages items
	 *
	 * @return	integer
	 * @since	1.6
	 */
	public function getTotal()
	{
		if (is_null($this->total)) {
			$langlist = $this->getLanguageList();
			$this->total = count($langlist);
		}

		return $this->total;
	}

	/**
	 * Method to set the default language
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function publish($cid)
	{
		if ($cid) {
			$client	= $this->getClient();

			$params = JComponentHelper::getParams('com_languages');
			$params->set($client->name, $cid);

			$table = JTable::getInstance('extension');
			$id = $table->find(array('element' => 'com_languages'));

			// Load
			if (!$table->load($id)) {
				$this->setError($table->getError());
				return false;
			}

			$table->params = (string)$params;
			// pre-save checks
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// save the changes
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
		}
		else {
			$this->setError(JText::_('COM_LANGUAGES_ERR_NO_LANGUAGE_SELECTED'));
			return false;
		}

		// Clean the cache of com_languages and component cache.
		$this->cleanCache();
		$this->cleanCache('_system');

		return true;
	}

	/**
	 * Method to get the folders
	 *
	 * @return	array	Languages folders
	 * @since	1.6
	 */
	protected function getFolders()
	{
		if (is_null($this->folders)) {
			$path = $this->getPath();
			jimport('joomla.filesystem.folder');
			$this->folders = JFolder::folders($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'pdf_fonts', 'overrides'));
		}

		return $this->folders;
	}

	/**
	 * Method to get the path
	 *
	 * @return	string	The path to the languages folders
	 * @since	1.6
	 */
	protected function getPath()
	{
		if (is_null($this->path)) {
			$client = $this->getClient();
			$this->path = JLanguage::getLanguagePath($client->path);
		}

		return $this->path;
	}

	/**
	 * Method to compare two languages in order to sort them
	 *
	 * @param	object	$lang1 the first language
	 * @param	object	$lang2 the second language
	 *
	 * @return	integer
	 * @since	1.6
	 */
	protected function compareLanguages($lang1, $lang2)
	{
		return strcmp($lang1->name, $lang2->name);
	}
}
