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
 * Categories Component Categories Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class ContentModelCategories extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.categories';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_content';

	/**
	 * Overridden method to lazy load data from the request/session as necessary
	 *
	 * @access	public
	 * @param	string	$key		The key of the state item to return
	 * @param	mixed	$default	The default value to return if it does not exist
	 * @return	mixed	The requested value by key
	 * @since	1.0
	 */
	function _populateState()
	{
		$app = &JFactory::getApplication();

		$this->setState('filter.extension', $this->_extension);

		// Get the parent id
		$parentId = JRequest::getInt('id');
		$this->setState('list.parentId', $parentId);

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		$limitstart = $app->getUserStateFromRequest($this->_context.'.limitstart', 'limitstart', 0);
		$this->setState('list.limitstart', $limitstart);

		$orderCol = $app->getUserStateFromRequest($this->_context.'.ordercol', 'filter_order', 'a.lft');
		$this->setState('list.ordering', $orderCol);

		$orderDirn = $app->getUserStateFromRequest($this->_context.'.orderdirn', 'filter_order_Dir', 'asc');
		$this->setState('list.direction', $orderDirn);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
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
	 */
	public function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('filter.extension');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');

		return md5($id);
	}

	/**
	 * @param	boolean	True to join selected foreign information
	 *
	 * @return	string
	 */
	function _getListQuery($resolveFKs = true)
	{
		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.access, a.published, a.access, a.ordering' .
				', a.path, a.parent_id, a.level, a.lft, a.rgt' .
				', a.description'
			)
		);
		$query->from('#__categories AS a');

		// Retrieve a sub tree
		if ($parentId = $this->getState('list.parentId'))
		{
			$query->join('LEFT', '#__categories AS p ON p.id = '.(int) $parentId);
			$query->where('(a.lft > p.lft AND a.rgt < p.rgt)');
		}

		// Filter by extension
		if ($extension = $this->getState('filter.extension')) {
			$query->where('a.extension = '.$this->_db->quote($extension));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$user	= &JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());
			$query->where('a.access IN ('.$groups.')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		}
		else if (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$query->where('a.published IN ('.$published.')');
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.title')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
