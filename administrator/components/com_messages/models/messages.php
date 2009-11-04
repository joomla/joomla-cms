<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Messages Component Messages Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesModelMessages extends JModelList
{
	/**
	 * Model context string
	 *
	 * @var string
	 */
	protected $_context = 'com_messages.list';

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
		$app =& JFactory::getApplication('administrator');

		$filter_order = $app->getUserStateFromRequest($this->_context.'.filter_order',	'filter_order',		'a.date_time',	'cmd');
		$this->setState('list.ordering', $filter_order);

		$filter_order_Dir = $app->getUserStateFromRequest($this->_context.'.filter_order_Dir','filter_order_Dir',	'DESC',			'word');
		$this->setState('list.direction', $filter_order_Dir);

		$limit = $app->getUserStateFromRequest('global.list.limit',			'limit',			$app->getCfg('list_limit'), 'int');
		$this->setState('list.limit', $limit);

		$limitstart			= $app->getUserStateFromRequest($this->_context.'.limitstart',		'limitstart',		0,				'int');
		$this->setState('list.start', $limitstart);

		$search = $app->getUserStateFromRequest($this->_context.'search',			'search',			'',				'string');
		$this->setState('filter.search', $search);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 * @since	1.6
	 */
	protected function _getListQuery()
	{
		$user =& JFactory::getUser();
		$query = new JQuery;

		$query->select($this->getState('list.select', 'a.*'));
		$query->select('u.name AS user_from');
		$query->from('#__messages AS a');

		$query->join('INNER', '#__users AS u ON u.id = a.user_id_from');
		$query->where('a.user_id_to = '.(int) $user->get('id'));

		$search = $this->getState('filter.search');

		if ($search != '') {
			$searchEscaped = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%', false);
			$query->where('a.subject LIKE '.$searchEscaped.' OR a.message LIKE '.$searchEscaped.')');
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.date_time')).' '.$this->_db->getEscaped($this->getState('list.direction', 'DESC')));

		return $query;
	}
}