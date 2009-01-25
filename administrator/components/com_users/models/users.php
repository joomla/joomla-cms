<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_prototypelist.php';

/**
 * @package		Joomla.Administrator
 * @subpackage	com_user
 */
class UserModelUsers extends UserModelPrototypeList
{
	/**
	 * Valid types
	 */
	function isValidType($type)
	{
		$types	= array('aro', 'axo');
		return in_array(strtolower($type), $types);
	}

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

			$search		= $app->getUserStateFromRequest('users.user.search',		'search');
			$groupId	= $app->getUserStateFromRequest('users.user.groupId',		'filter_group_id');
			$loggedIn	= $app->getUserStateFromRequest('users.user.loggedIn',		'filter_logged_in');
			$enabled	= $app->getUserStateFromRequest('users.user.enabled',		'filter_enabled', '*');
			$activated	= $app->getUserStateFromRequest('users.user.activated',		'filter_activated', '*');
			$limit		= $app->getUserStateFromRequest('global.list.limit', 		'limit', $app->getCfg('list_limit'));
			$limitstart	= $app->getUserStateFromRequest('users.user.limitstart',	'limitstart', 0);
			$orderCol	= $app->getUserStateFromRequest('users.user.ordercol',		'filter_order', 'a.name');
			$orderDirn	= $app->getUserStateFromRequest('users.user.orderdirn',		'filter_order_Dir', 'asc');

			$this->setState('list.search',		$search);
			$this->setState('list.group_id',	$groupId);
			$this->setState('list.logged_in',	$loggedIn);
			$this->setState('list.enabled',		$enabled);
			$this->setState('list.activated',	$activated);
			$this->setState('list.limit',		$limit);
			$this->setState('list.start',		$limitstart);
			$this->setState('orderCol',			$orderCol);
			$this->setState('orderDirn',		$orderDirn);

			if ($orderCol) {
				$this->setState('list.order',	$orderCol.' '.($orderDirn == 'asc' ? 'asc' : 'desc'));
			}

			$this->__state_set = true;
		}
		return parent::getState($key, $default);
	}

	/**
	 * Gets a list of objects
	 *
	 * @param	boolean	True to resolve foreign keys
	 *
	 * @return	string
	 */
	function _getListQuery($resolveFKs = false)
	{
		if (empty($this->_list_query))
		{
			$db	= &$this->getDBO();
			$query = new JQuery;
			$groupId	= $this->getState('list.group_id');
			$loggedIn	= $this->getState('list.logged_in');
			$enabled	= $this->getState('list.enabled');
			$activated	= $this->getState('list.activated');
			$select		= $this->getState('list.select', 'a.*');
			$search		= $this->getState('list.search');
			$where		= $this->getState('list.where');
			$orderBy	= $this->getState('list.order');

			$query->select($select);
			$query->from('#__users AS a');

			if ($resolveFKs) {
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

			$this->_list_query = (string) $query;
			//echo str_replace('#__','jos_',nl2br($this->_list_query));
		}

		return $this->_list_query;
	}
}