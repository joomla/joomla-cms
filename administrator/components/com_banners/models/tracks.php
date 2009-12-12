<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Methods supporting a list of tracks.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersModelTracks extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_banners.tracks';
	/**
	 *
	 */
	protected $_basename;
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$type = $app->getUserStateFromRequest($this->_context.'.filter.type', 'filter_type');
		$this->setState('filter.type', $type);

		$begin = $app->getUserStateFromRequest($this->_context.'.filter.begin', 'filter_begin', '', 'string');
		$this->setState('filter.begin', $begin);

		$end = $app->getUserStateFromRequest($this->_context.'.filter.end', 'filter_end', '', 'string');
		$this->setState('filter.end', $end);

		$categoryId = $app->getUserStateFromRequest($this->_context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$clientId = $app->getUserStateFromRequest($this->_context.'.filter.client_id', 'filter_client_id', '');
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_banners');
		$this->setState('params', $params);

		// List state information.
		parent::_populateState('name', 'asc');
	}
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JQuery
	 */
	protected function _getListQuery()
	{
		// Get the application object
		$app = &JFactory::getApplication();

		require_once JPATH_COMPONENT . '/helpers/banners.php';

		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select(
				'a.track_date as track_date,'.
				'a.track_type as track_type,'.
				'a.`count` as `count`'
		);
		$query->from('`#__banner_tracks` AS a');

		// Join with the banners
		$query->join('LEFT','`#__banners` as b ON b.id=a.banner_id');
		$query->select('b.name as name');

		// Join with the client
		$query->join('LEFT','`#__banner_clients` as cl ON cl.id=b.cid');
		$query->select('cl.name as client_name');

		// Join with the category
		$query->join('LEFT','`#__categories` as cat ON cat.id=b.catid');
		$query->select('cat.title as category_title');

		// Filter by type
		$type = $this->getState('filter.type');
		if (!empty($type)) {
			$query->where('a.track_type = '.(int) $type);
		}

		// Filter by client
		$clientId = $this->getState('filter.client_id');
		if (is_numeric($clientId)) {
			$query->where('b.cid = '.(int) $clientId);
		}

		// Filter by category
		$catedoryId = $this->getState('filter.category_id');
		if (is_numeric($catedoryId)) {
			$query->where('b.catid = '.(int) $catedoryId);
		}

		// Filter by begin date

		$begin = $this->getState('filter.begin');
		if (!empty($begin)) {
			$query->where('a.track_date >= '.$this->_db->Quote($begin));
		}

		// Filter by end date
		$end = $this->getState('filter.end');
		if (!empty($end)) {
			$query->where('a.track_date <= '.$this->_db->Quote($end));
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'name');
		$query->order($this->_db->getEscaped($orderCol).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		return $query;
	}
	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete()
	{
		// Initialise variables
		$user	= JFactory::getUser();

		// Access checks.
		$categoryId=$this->getState('category_id');
		if ($categoryId) {
			$allow = $user->authorise('core.delete', 'com_banners.category.'.(int) $categoryId);
		}
		else {
			$allow = $user->authorise('core.delete', 'com_banners');
		}

		if ($allow)
		{
			// Delete tracks from this banner
			$query = new JQuery;
			$query->delete();
			$query->from('`#__banner_tracks`');

			// Filter by type
			$type = $this->getState('filter.type');
			if (!empty($type)) {
				$query->where('track_type = '.(int) $type);
			}

			// Filter by begin date
			$begin = $this->getState('filter.begin');
			if (!empty($begin)) {
				$query->where('track_date >= '.$this->_db->Quote($begin));
			}

			// Filter by end date
			$end = $this->getState('filter.end');
			if (!empty($end)) {
				$query->where('track_date <= '.$this->_db->Quote($end));
			}

			$where='1';
			// Filter by client
			$clientId = $this->getState('filter.client_id');
			if (!empty($clientId)) {
				$where.=' AND cid = '.(int) $clientId;
			}
			// Filter by category
			if (!empty($categoryId)) {
				$where.=' AND catid = '.(int) $categoryId;
			}

			$query->where('banner_id IN (SELECT id FROM `#__banners` WHERE '.$where.')');

			$this->_db->setQuery((string)$query);
			$this->setError((string)$query);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		else
		{
			JError::raiseWarning(403, JText::_('JError_Core_Delete_not_permitted'));
		}

		return true;
	}
	/**
	 * Get file name
	 *
	 * @return string the file name
	 */
	public function getBaseName()
	{
		if(!isset($this->_basename))
		{
			$app = &JFactory::getApplication();
			$basename = $this->getState('basename');
			$basename = str_replace('__SITE__',$app->getCfg('sitename'),$basename);
			$categoryId = $this->getState('filter.category_id');
			if (is_numeric($categoryId))
			{
				if ($categoryId>0)
				{
					$basename = str_replace('__CATID__',$categoryId,$basename);
				}
				else
				{
					$basename = str_replace('__CATID__','',$basename);
				}
				$categoryName = $this->getCategoryName();
				$basename = str_replace('__CATNAME__',$categoryName,$basename);
			}
			else
			{
				$basename = str_replace('__CATID__','',$basename);
				$basename = str_replace('__CATNAME__','',$basename);
			}
			$clientId = $this->getState('filter.client_id');
			if (is_numeric($clientId))
			{
				if ($clientId>0)
				{
					$basename = str_replace('__CLIENTID__',$clientId,$basename);
				}
				else
				{
					$basename = str_replace('__CLIENTID__','',$basename);
				}
				$clientName = $this->getClientName();
				$basename = str_replace('__CLIENTNAME__',$clientName,$basename);
			}
			else
			{
				$basename = str_replace('__CLIENTID__','',$basename);
				$basename = str_replace('__CLIENTNAME__','',$basename);
			}
			$type = $this->getState('filter.type');
			if ($type > 0)
			{
				$basename = str_replace('__TYPE__',$type,$basename);
				$typeName = JText::_('Banners_Type'.$type);
				$basename = str_replace('__TYPENAME__',$typeName,$basename);
			}
			else
			{
				$basename = str_replace('__TYPE__','',$basename);
				$basename = str_replace('__TYPENAME__','',$basename);
			}
			$begin = $this->getState('filter.begin');
			if (!empty($begin))
			{
				$basename = str_replace('__BEGIN__',$begin,$basename);
			}
			else
			{
				$basename = str_replace('__BEGIN__','',$basename);
			}
			$end = $this->getState('filter.end');
			if (!empty($end))
			{
				$basename = str_replace('__END__',$end,$basename);
			}
			else
			{
				$basename = str_replace('__END__','',$basename);
			}
			$this->_basename = $basename;
		}
		return $this->_basename;
	}
	/**
	 * Get the category name
	 *
	 * @return string the category name
	 */
	protected function getCategoryName()
	{
		$categoryId = $this->getState('filter.category_id');
		if ($categoryId)
		{
			$query = new JQuery;
			$query->select('title');
			$query->from('`#__categories`');
			$query->where('`id`='.$this->_db->quote($categoryId));
			$this->_db->setQuery((string)$query);
			$name = $this->_db->loadResult();
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		else
		{
			$name = JText::_('Banners_NoCategoryName');
		}
		return $name;
	}
	/**
	 * Get the category name
	 *
	 * @return string the category name
	 */
	protected function getClientName()
	{
		$clientId = $this->getState('filter.client_id');
		if ($clientId)
		{
			$query = new JQuery;
			$query->select('name');
			$query->from('`#__banner_clients`');
			$query->where('`id`='.$this->_db->quote($clientId));
			$this->_db->setQuery((string)$query);
			$name = $this->_db->loadResult();
			if ($this->_db->getErrorNum())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		else
		{
			$name = JText::_('Banners_NoClientName');
		}
		return $name;
	}
	/**
	 * Get the file type
	 *
	 * @return string the file type
	 */
	public function getFileType()
	{
		return $this->getState('compressed')?'zip':'csv';
	}
	/**
	 * Get the mime type
	 *
	 * @return string the mime type
	 */
	public function getMimeType()
	{
		return $this->getState('compressed')?'application/zip':'text/csv';
	}
	/**
	 * Get the content
	 *
	 * @return string the content
	 */
	public function getContent()
	{
		if (!isset($this->_content))
		{
			$this->_content = '';
			$this->_content.=
			'"'.str_replace('"','""',JText::_('Banners_Heading_Name')).'","'.
				str_replace('"','""',JText::_('Banners_Heading_Client')).'","'.
				str_replace('"','""',JText::_('JGrid_Heading_Category')).'","'.
				str_replace('"','""',JText::_('Banners_Heading_Type')).'","'.
				str_replace('"','""',JText::_('Banners_Heading_Count')).'","'.
				str_replace('"','""',JText::_('Banners_Heading_Date')).'"'."\n";
			foreach($this->getItems() as $item)
			{
				$this->_content.=
				'"'.str_replace('"','""',$item->name).'","'.
					str_replace('"','""',$item->client_name).'","'.
					str_replace('"','""',$item->category_title).'","'.
					str_replace('"','""',($item->track_type==1 ? JText::_('Banners_Impression'): JText::_('Banners_Click'))).'","'.
					str_replace('"','""',$item->count).'","'.
					str_replace('"','""',$item->track_date).'"'."\n";
			}
			if ($this->getState('compressed'))
			{
				$files = array();
				$files['track']=array();
				$files['track']['name'] = $this->getBasename() . '.csv';
				$files['track']['data'] = $this->_content;
				$files['track']['time'] = time();
				$ziproot = JPATH_ROOT . '/tmp/' . uniqid('banners_tracks_') . '.zip';
				// run the packager
				jimport('joomla.filesystem.folder');
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.archive');
				$delete = JFolder::files(JPATH_ROOT . '/tmp/', 'banners_tracks_',false,true);
				if (!empty($delete)) {
					if (!JFile::delete($delete)) {
						// JFile::delete throws an error
						$this->setError(JText::_('BANNERS_ZIP_DELETE_FAILURE'));
						return false;
					}
				}
				if (!$packager = & JArchive::getAdapter('zip')) {
					$this->setError(JText::_('BANNERS_ZIP_ADAPTER_FAILURE'));
					return false;
				} else if (!$packager->create($ziproot, $files)) {
					$this->setError(JText::_('BANNERS_ZIP_CREATE_FAILURE'));
					return false;
				}
				$this->_content = file_get_contents($ziproot);
			}
		}
		return $this->_content;
	}
}

