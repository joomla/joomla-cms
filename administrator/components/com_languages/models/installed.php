<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	 * @var array languages folders
	 */
	protected $folders = null;

	/**
	 * @var string language path
	 */
	protected $path = null;

	/**
	 * Model context string.
	 *
	 * @access	protected
	 * @var		string
	 */
	 protected $_context = 'com_languages.installed';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _populateState()
	{
		// Initialize variables.
		$app		= &JFactory::getApplication('administrator');
		$params		= JComponentHelper::getParams('com_languages');

		// Load the filter state.
		$clientId = $app->getUserStateFromRequest($this->_context.'.filter.client_id', 'filter_client_id', 0);
		$this->setState('filter.client_id', $clientId);

		// List state information.
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
		$this->setState('list.limitstart', $limitstart);

		$orderCol	= $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', 'a.title');
		$this->setState('list.ordering', $orderCol);

		$orderDirn	= $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', 'asc');
		$this->setState('list.direction', $orderDirn);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.client_id');
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');

		return md5($id);
	}

	/**
	 * Method to get the client object
	 *
	 * @return object
	 */
	public function &getClient()
	{
		if (is_null($this->client)) {
			$this->client = &JApplicationHelper::getClientInfo($this->getState('filter.client_id', 0));
		}
		return $this->client;
	}

	/**
	 * Method to get the ftp credentials
	 *
	 * @return object
	 */
	public function &getFtp()
	{
		if (is_null($this->ftp))
		{
			jimport('joomla.client.helper');
			$this->ftp = &JClientHelper::setCredentialsFromRequest('ftp');
		}
		return $this->ftp;
	}

	/**
	 * Method to get the option
	 *
	 * @return object
	 */
	public function &getOption()
	{
		$option = $this->getState('option');
		return $option;
	}

	/**
	 * Method to get Languages item data
	 *
	 * @return array
	 */
	public function &getData()
	{
		if (is_null($this->data))
		{
			// Get information
			$folders	= &$this->_getFolders();
			$path		= &$this->_getPath();
			$client		= &$this->getClient();

			// Compute all the languages
			$data	= array ();
			foreach ($folders as $folder)
			{
				$file = $path.DS.$folder.DS.$folder.'.xml';
				$info = & JApplicationHelper::parseXMLLangMetaFile($file);
				$row = new JObject();
				$row->language 	= $folder;

				if (!is_array($info)) {
					continue;
				}
				foreach($info as $key => $value) {
					$row->$key = $value;
				}
				// if current than set published
				$params = &JComponentHelper::getParams('com_languages');
				if ($params->get($client->name, 'en-GB') == $row->language) {
					$row->published	= 1;
				}
				else {
					$row->published = 0;
				}

				$row->checked_out = 0;
				$data[] = $row;
			}
			usort($data,array('LanguagesModelInstalled','_compareLanguages'));

			// Prepare data
			$limit = $this->getState('list.limit');
			$start = $this->getState('list.limitstart');
			$total = $this->getTotal();

			if ($limit == 0)
			{
				$start = 0;
				$end = $total;
			}
			else
			{
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
			for ($i = $start;$i < $end;$i++) {
				$this->data[] = & $data[$i];
			}
		}
		return $this->data;
	}

	/**
	 * Method to get the total number of Languages items
	 *
	 * @return integer
	 */
	public function &getTotal()
	{
		if (is_null($this->total))
		{
			$folders = & $this->_getFolders();
			$this->total = count($folders);
		}
		return $this->total;
	}

	/**
	 * Method to set the default language
	 *
	 * return boolean
	 */
	public function publish()
	{
		$cid = $this->getState('cid');
		if (count($cid)>0) {
			$client	= & $this->getClient();

			$params = & JComponentHelper::getParams('com_languages');
			$params->set($client->name, $cid[0]);

			$table = & JTable::getInstance('component');
			$table->loadByOption('com_languages');

			$table->params = $params->toString();
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
			$this->setError(JText::_('Langs_No_Language_Selected'));
			return false;
		}
		return true;
	}

	/**
	 * Method to get the folders
	 *
	 * @access protected
	 * @return array languages folders
	 */
	protected function _getFolders()
	{
		if (is_null($this->folders))
		{
			$path = & $this->_getPath();
			jimport('joomla.filesystem.folder');
			$this->folders = &JFolder::folders($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'pdf_fonts','overrides'));
		}
		return $this->folders;
	}

	/**
	 * Method to get the path
	 *
	 * @access protected
	 * @return string the path to the languages folders
	 */
	protected function _getPath()
	{
		if (is_null($this->path))
		{
			$client = &$this->getClient();
			$this->path = &JLanguage::getLanguagePath($client->path);
		}
		return $this->path;
	}

	/**
	 * Method to compare two languages in order to sort them
	 *
	 * @access protected
	 * @param object $lang1 the first language
	 * @param object $lang2 the second language
	 * @return integer
	 */
	protected function _compareLanguages($lang1,$lang2)
	{
		return strcmp($lang1->name,$lang2->name);
	}
}