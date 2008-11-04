<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_prototypelist.php';

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessModelGroups extends AccessModelPrototypeList
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
			$app 		= &JFactory::getApplication();
			$type		= $this->_state->get('list.group_type');
			$context	= 'ac.groups.'.$type;

			$type		= $app->getUserStateFromRequest($context.'.type',		'group_type');
			$search		= $app->getUserStateFromRequest($context.'.search',		'search');
			$limit 		= $app->getUserStateFromRequest('global.list.limit',	'limit',			$app->getCfg('list_limit'));
			$limitstart = $app->getUserStateFromRequest($context.'.limitstart',	'limitstart',		0);
			$orderCol	= $app->getUserStateFromRequest($context.'.ordercol',	'filter_order',		'a.lft');
			$orderDirn	= $app->getUserStateFromRequest($context.'.orderdirn',	'filter_order_Dir',	'asc');

			$this->setState('list.search',	$search);
			$this->setState('list.limit',	$limit);
			$this->setState('list.start',	$limitstart);
			if ($orderCol) {
				$this->setState('list.order',	$orderCol.' '.($orderDirn == 'asc' ? 'asc' : 'desc'));
			}
			$this->setState('orderCol',		$orderCol);
			$this->setState('orderDirn',	$orderDirn);

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
			$db			= &$this->getDBO();
			$query		= new JQuery;
			$type		= strtolower($this->getState('list.group_type', 'aro'));
			$tree		= $this->getState('list.tree');
			$parentId	= $this->getState('list.parent_id');
			$select		= $this->getState('list.select', 'a.*');
			$search		= $this->getState('list.search');
			$where		= $this->getState('list.where');
			$orderBy	= $this->getState('list.order');

			// Dynamically determine the table
			$table		= '#__core_acl_'.$type.'_groups';

			$query->select($select);
			$query->from($table.' AS a');

			// Add the level in the tree
			if ($tree) {
				$query->select('COUNT(DISTINCT c2.id) AS level');
				$query->join('LEFT OUTER', $table.' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt');
				$query->group('a.id');
			}

			// Get a subtree below the parent
			if ($parentId > 0) {
				$query->join('LEFT', $table.' AS p ON p.id = '.(int) $parentId);
				$query->where('a.lft > p.lft AND a.rgt < p.rgt');
			}

			// Resolve associated data
			if ($resolveFKs)
			{
				// Count the objects in the user group
				if ($type == 'aro') {
					$query->select('COUNT(DISTINCT map.aro_id) AS object_count');
					$query->join('LEFT', '#__core_acl_groups_'.$type.'_map AS map ON map.group_id=a.id');
					$query->group('a.id');
				}
				// Count the items in the access level
				else if ($type == 'axo') {
					$query->select('COUNT(DISTINCT map.axo_id) AS object_count');
					$query->join('LEFT', '#__core_acl_groups_'.$type.'_map AS map ON map.group_id=a.id');
					$query->group('a.id');
				}
			}

			// Search in the group name
			if ($search) {
				$serach = $db->Quote('%'.$db->getEscaped($search, true).'%', false);
				$query->where('a.name LIKE '.$serach);
			}

			// An abritrary where clause
			if ($where) {
				$query->where($where);
			}

			if ($orderBy) {
				$query->order($this->_db->getEscaped($orderBy));
			}

			echo nl2br($query->toString());
			$this->_list_query = (string) $query;
		}

		return $this->_list_query;
	}

	/**
	 * Utility method to gets the level of a group
	 */
	function getLevel($id = null, $type = 'aro')
	{
		$model = new AccessModelGroups(array('ignore_request' => true));
		$model->setState('list.select',		'a.id');
		$model->setState('list.group_type',	$type);
		$model->setState('list.tree',		true);
		$model->setState('list.where',		'a.id = '.(int) $id);
		$result = $model->getList(false);
		return isset($result[0]) ? $result[0]->level : false;
	}

}