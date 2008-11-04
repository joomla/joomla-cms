<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_prototype.php';

/**
 * @package		Users
 * @subpackage	com_users
 */
class UserModelUser extends UserModelPrototype
{
	/**
	 * Overridden method to lazy load data from the request/session as necessary
	 *
	 * @access	public
	 * @param	string	$key		The key of the state item to return
	 * @param	mixed	$default	The default value to return if it does not exist
	 * @return	mixed	The requested value by key
	 * @since	1.0
	 */
	function getState($key=null, $default=null)
	{
		if (empty($this->__state_set))
		{
			$app = &JFactory::getApplication();

			$cid	= JRequest::getVar('cid', array(0), '', 'array');
			$id		= JRequest::getInt('id', $cid[0]);
			$this->setState('id', $id);

			$search = $app->getUserStateFromRequest('users.user.search', 'search');
			$this->setState('search', $search);

			//$published 	= $app->getUserStateFromRequest('users.user.published', 'published', 1);
			//$this->setState('published', ($published == '*' ? null : $published));

			$value = $app->getUserStateFromRequest('users.user.groupId', 'filter_group_id');
			$this->setState('group_id', $value);

			$value = $app->getUserStateFromRequest('users.user.loggedIn', 'filter_logged_in');
			$this->setState('logged_in', $value);

			$value = $app->getUserStateFromRequest('users.user.enabled', 'filter_enabled', '*');
			$this->setState('enabled', $value);

			$value = $app->getUserStateFromRequest('users.user.activated', 'filter_activated', '*');
			$this->setState('activated', $value);

			// List state information
			$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
			$this->setState('limit', $limit);

			$limitstart = $app->getUserStateFromRequest('users.user.limitstart', 'limitstart', 0);
			$this->setState('limitstart', $limitstart);

			$orderCol	= $app->getUserStateFromRequest('users.user.ordercol', 'filter_order', 'a.name');
			$orderDirn	= $app->getUserStateFromRequest('users.user.orderdirn', 'filter_order_Dir', 'asc');
			if ($orderCol) {
				$this->setState('order by',	$orderCol.' '.($orderDirn == 'asc' ? 'asc' : 'desc'));
			}
			$this->setState('orderCol',	$orderCol);
			$this->setState('orderDirn',	$orderDirn);

			$this->__state_set = true;
		}
		return parent::getState($key, $default);
	}


	/**
	 * Proxy for getTable
	 */
	function &getTable()
	{
		return parent::getTable('User', 'JTable');
	}

	/**
	 * @return	JUser
	 */
	function &getItem()
	{
		$session = &JFactory::getSession();
		$id = (int) $session->get('users.'.$this->getName().'.id', $this->getState('id'));

		$user	= &JUser::getInstance($id);
		return $user;
	}


	/**
	 * Gets a list of categories objects
	 *
	 * Filters may be fields|published|order by|searchName|where
	 * @param array Named array of field-value filters
	 * @param boolean True if foreign keys are to be resolved
	 */
	function _getListQuery($filters, $resolveFKs=false)
	{
		$groupId		= $filters->get('group_id');
		$loggedIn		= $filters->get('logged_in');
		$enabled		= $filters->get('enabled');
		$activated		= $filters->get('activated');
		// arbitrary where
		$select			= $filters->get('select');
		$search			= $filters->get('search');
		$where			= $filters->get('where');
		$orderBy		= $filters->get('order by');

		$db	= &$this->getDBO();
		$query = new JQuery;

		$query->select($select !== null ? $select : 'a.*' );
		$query->from('#__users AS a');

		if ($resolveFKs) {
			/*
			// checked out
			$query->select('co.name AS editor');
			$query->join('LEFT', '#__users AS co ON co.id=a.checked_out');

			// access level
			$config	= &JComponentHelper::getParams('com_users');
			if ($config->get('acl_mode') == 0) {
				$query->select('g.name AS access_name');
				$query->join('LEFT', '#__core_acl_axo_groups AS g ON g.value=a.access');
			}
			else {
				$query->select('g.name AS access_name');
				$query->join('LEFT', '#__core_acl_axo_groups AS g ON g.value=CAST(a.access AS CHAR)');
			}
*/
			$NL = $db->Quote("\n");
			$query->select('GROUP_CONCAT(DISTINCT(g.name) SEPARATOR '.$NL.') AS groups');
			$query->join('INNER', '#__core_acl_aro AS aro ON aro.value = a.id');
			$query->join('INNER', '#__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id');
			$query->join('INNER', '#__core_acl_aro_groups AS g ON g.id = gm.group_id');
			$query->group('a.id');

			/* @todo Check for performance on this join  - there is an index on userid ?? */
			$query->select('s.userid AS loggedin');
			if ($loggedIn) {
				$query->join('INNER', '#__session AS s ON s.userid = a.id');
			}
			else {
				$query->join('LEFT', '#__session AS s ON s.userid = a.id');
			}
		}

		// options
		if ($search) {
			$match = $db->Quote('%'.$db->getEscaped($search, true).'%', false);
			$query->where('(a.name LIKE '.$match.' OR a.username LIKE '.$match.')');
		}

		if ($groupId) {
			if (!$resolveFKs) {
				$query->join('INNER', '#__core_acl_aro AS aro ON aro.value = a.id');
				$query->join('INNER', '#__core_acl_groups_aro_map AS gm ON gm.aro_id = aro.id');
			}
			$query->where('gm.group_id = '.(int) $groupId);
		}

		if (is_numeric($enabled)) {
			$query->where('a.block = '.$enabled);
		}

		if (is_numeric($activated)) {
			if ($activated == 1) {
				$query->where('a.activation = '.$db->Quote(''));
			}
			else {
				$query->where('a.activation <> '.$db->Quote(''));
			}
		}

		if ($where) {
			$query->where($where);
		}

		if ($orderBy) {
			$query->order($this->_db->getEscaped($orderBy));
		}

		//echo str_replace('#__','jos_',nl2br($query->toString()));
		return $query;
	}

	/**
	 * Perform batch operations
	 *
	 * @param	array	An array of variable for the batch operation
	 * @param	array	An array of IDs on which to operate
	 */
	function batch($vars, $ids)
	{
		$db		= $this->getDBO();
		$result	= true;

		JArrayHelper::toInteger($ids);

		// Do stuff

		return $result;
	}
}