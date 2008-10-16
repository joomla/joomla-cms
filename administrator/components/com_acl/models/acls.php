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

require_once JPATH_COMPONENT.DS.'models'.DS.'_prototypelist.php';

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessModelACLs extends AccessModelPrototypeList
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

			$search		= $app->getUserStateFromRequest('acl.rules.search', 'search');
			$limit 		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
			$limitstart = $app->getUserStateFromRequest('acl.rules.limitstart', 'limitstart', 0);
			$orderCol	= $app->getUserStateFromRequest('acl.rules.ordercol', 'filter_order', 'a.id');
			$orderDirn	= $app->getUserStateFromRequest('acl.rules.orderdirn', 'filter_order_Dir', 'asc');

			$this->setState('list.search', $search);
			$this->setState('list.limit', $limit);
			$this->setState('list.start', $limitstart);
			if ($orderCol) {
				$this->setState('list.order',	$orderCol.' '.($orderDirn == 'asc' ? 'asc' : 'desc'));
			}
			$this->setState('orderCol',	$orderCol);
			$this->setState('orderDirn',	$orderDirn);

			$this->__state_set = true;
		}
		return parent::getState($key, $default);
	}

	function getExtendedItems($items = null)
	{
		if ($items == null) {
			$items = $this->getList();
		}
		if (!is_array($items)) {
			$items = array($items);
		}

		// first pass, get the id's
		$n		= count($items);
		$aclIds	= array();
		$rlu	= array();
		for ($i = 0; $i < $n; $i++)
		{
			$aclIds[]				= $items[$i]->id;
			$rlu[$items[$i]->id]	= $i;
		}

		$db		= &$this->getDBO();
		$acls	= array();

		// run sql to get ACO's, ARO's and AXO's
		if (!empty($aclIds))
		{
			$ids = implode(',', $aclIds);
			foreach (array('aco', 'aro', 'axo') as $type)
			{
				$query = 'SELECT	a.acl_id,o.name,s.name AS section_name' .
						' FROM	#__core_acl_'. $type .'_map a' .
						' INNER JOIN #__core_acl_'. $type .' o ON (o.section_value=a.section_value AND o.value=a.value)' .
						' INNER JOIN #__core_acl_'. $type . '_sections s ON s.value=a.section_value' .
						' WHERE	a.acl_id IN ('. $ids . ')';
				$db->setQuery($query);
				$temp = $db->loadObjectList();
				foreach ($temp as $item)
				{
					$i	= $rlu[$item->acl_id];
					$k	= $type.'s';

					if (!isset($items[$i]->$k)) {
						$items[$i]->$k = array();
					}
					$r = &$items[$i]->$k;
					$r[$item->section_name][] = $item->name;
				}
			}

			// grab ARO and AXO groups
			foreach (array('aro', 'axo') as $type)
			{
				$query = 'SELECT a.acl_id,g.name' .
						' FROM #__core_acl_'. $type .'_groups_map a' .
						' INNER JOIN #__core_acl_'. $type .'_groups g ON g.id=a.group_id' .
						' WHERE	a.acl_id IN ('. $ids . ')';
				$db->setQuery($query);
				$temp	= $db->loadObjectList();
				foreach ($temp as $item)
				{
					$i	= $rlu[$item->acl_id];
					$k	= $type.'Groups';
					if (!isset($items[$i]->$k)) {
						$items[$i]->$type = array();
					}
					$r = &$items[$i]->$k;
					$r[] = $item->name;
				}
			}
		}
		return $items;
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
			$select		= $this->getState('list.select', 'a.*');
			$section	= $this->getState('list.section_value');
			$search		= $this->getState('list.search');
			$orderBy	= $this->getState('list.order');
			$aclType	= $this->getState('list.acl_type');

			$query->select($select);
			$query->from('#__core_acl_acl AS a');

			if ($resolveFKs) {
			}

			// Filter on section_value
			if ($section) {
				if (is_array($section)) {
					foreach ($section as $k => $v) {
						$section[$k] = $db->Quote($v);
					}
					$query->where('a.section_value IN ('.implode(',', $section).')');
				}
				else {
					$query->where('a.section_value = '.$db->Quote($section));
				}
			}

			// Search in note
			if ($search) {
				$serach = $db->Quote('%'.$db->getEscaped($search, true).'%', false);
				$query->where('a.note LIKE '.$serach);
			}

			if ($orderBy) {
				$query->order($db->getEscaped($orderBy));
			}

			if ($aclType !== null) {
				$query->where('a.acl_type = '.(int) $aclType);
			}

			//echo nl2br($query->toString());
			$this->_list_query = (string) $query;
		}

		return $this->_list_query;
	}
/*
	function getSections()
	{
		$model = JModel::getInstance('Section',	'AccessModel');
		$model->setState('list.select',			'a.value, a.name AS text');
		$model->setState('list.section_type',	'acl');
		$model->setState('list.order',			'a.order_value,a.name');
		return $model->getList();
	}

	function getACOs()
	{
		$model = JModel::getInstance('object',	'AccessModel');
		$model->setState('list.section_value',	$this->getState('section_value'));
		$model->setState('list.object_type',	'aco');
		$model->setState('list.hidden',			'0');
		$model->setState('list.order',			'a.section_value,a.order_value,a.name');
		if ($aclType = $this->getState('list.acl_type')) {
			$model->setState('list.where', 'a.acl_type = '.(int) $aclType);
		}
		return $model->getList();
	}

	function getAROGroups()
	{
		$model = JModel::getInstance('Group', 'AccessModel');
		$model->setState('list.group_type',	'aro');
		$model->setState('list.tree',		'1');
		$model->setState('list.parent_id',	CONTROL_USERS_ARO_ID);
		$model->setState('list.order',		'a.lft');
		return $model->getList();
	}

	function getAXOs()
	{
		$model = JModel::getInstance('object',	'AccessModel');
		$model->setState('list.section_value',	$this->getState('section_value'));
		$model->setState('list.object_type',	'axo');
		$model->setState('list.hidden',			'0');
		$model->setState('list.order',			'a.order_value,a.name');
		return  $model->getList();
	}

	function getAXOGroups()
	{
		$model = JModel::getInstance('Group',	'AccessModel');
		$model->setState('list.group_type',	'axo');
		$model->setState('list.tree',		'1');
		$model->setState('list.order',		'a.lft');
		$model->setState('list.parent_id',	1);
		return $model->getList();
	}
*/
}