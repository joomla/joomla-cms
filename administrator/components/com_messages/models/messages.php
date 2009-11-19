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
		$app = JFactory::getApplication('administrator');

		$search = $app->getUserStateFromRequest($this->_context.'search',			'search',			'',				'string');
		$this->setState('filter.search', $search);

		// List state information.
		parent::_populateState('a.date_time', 'dsc');
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