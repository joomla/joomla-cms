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
			$ruleType	= $app->getUserStateFromRequest('acl.rules.type', 'filter_type', '1');
			$ruleSection = $app->getUserStateFromRequest('acl.rules.section', 'filter_section', 'core');

			$this->setState('list.search',	$search);
			$this->setState('list.limit',	$limit);
			$this->setState('list.start',	$limitstart);
			if ($orderCol) {
				$this->setState('list.order',	$orderCol.' '.($orderDirn == 'asc' ? 'asc' : 'desc'));
			}
			$this->setState('orderCol',				$orderCol);
			$this->setState('orderDirn',			$orderDirn);
			$this->setState('list.acl_type', 		$ruleType);
			if ($ruleSection != '*') {
				$this->setState('list.section_value',	$ruleSection);
			}

			$this->__state_set = true;
		}
		return parent::getState($key, $default);
	}

	/**
	 * Method to get a list of items.
	 *
	 * @access	public
	 * @return	mixed	An array of objects on success, false on failure.
	 * @since	1.0
	 */
	function &getItems()
	{
		// Try to load the value from internal storage.
		if (!empty($this->_list_items)) {
			return $this->_list_items;
		}

		// Run the parent get items method.
		parent::getList();

		// If the items were successfully loaded, lets process them further.
		if (!empty($this->_list_items))
		{
			$rule = JTable::getInstance('Acl', 'JTable');

			for ($i = 0, $n = count($this->_list_items); $i < $n; $i++)
			{
				$rule->reset();
				$rule->load($this->_list_items[$i]->id);

				if ($references = &$rule->findReferences(true, true)) {
					$this->_list_items[$i]->references = $references;
				}
				else {
					// Non fatal but lets alert the user somethings amiss
					JError::raiseWarning(500, $rule->getError());

					jimport('joomla.acl.aclreferences');
					$this->_list_items[$i]->references = new JxAclReferences;
				}
			}
		}

		return $this->_list_items;
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