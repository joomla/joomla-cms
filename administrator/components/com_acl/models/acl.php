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

require_once dirname(__FILE__).DS.'_prototypeitem.php';

if (!defined('ACCESS_USERS_ARO_ID')) {
	define('ACCESS_USERS_ARO_ID', 28);
}

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessModelACL extends AccessModelPrototypeItem
{
	/**
	 * The current item
	 *
	 * @var JTableAcl
	 */
	protected $_item = null;

	/**
	 * Proxy for getTable
	 */
	function getTable()
	{
		return JTable::getInstance('ACL');
	}

	/**
	 * Get the native acl information
	 *
	 * @return	array
	 */
	function getACL()
	{
		// @todo - surely we can merge this with getExtendedItem (affect the edit view)
		$acl	= &JFactory::getACL();
		$sess	= &JFactory::getSession();
		$id		= (int) $sess->get('com_acl.acl.id', $this->getState('id'));
		$result	= $acl->get_acl($id);
		return $result;
	}

	/**
	 * @param	boolean	True to resolve foreign data relationship
	 *
	 * @return	JStdClass
	 */
	function &getItem()
	{
		if (empty($this->_item))
		{
			$session = &JFactory::getSession();
			$id = (int) $session->get('com_acl.acl.id', $this->getState('id'));

			$table = $this->getTable();
			if ($table->load($id)) {
				// @todo Cannot tell if JTable::load throw an error on a null return
				//$this->setError($table->getError());
			}
			$this->_item = JArrayHelper::toObject($table->getProperties(1), 'JStdClass');
		}
		return $this->_item;
	}

	/**
	 * Gets an ACL with extended data
	 *
	 * @return	JStdClass
	 */
	function getExtendedItem()
	{
		$item	= array($this->getItem());
		$model	= JModel::getInstance('Acls', 'AccessModel');
		$item	= $model->getExtendedItems($item);
		return $item[0];
	}

	function getSections()
	{
		$model = JModel::getInstance('Section', 'AccessModel');
		$model->setState('list.select',			'a.value, a.name AS text');
		$model->setState('list.section_type',	'acl');
		$model->setState('list.order',			'a.order_value,a.name');
		return $model->getList();
	}

	function getACOs()
	{
		//Model::addIncludePath(JPATH_COMPONENT.DS.'models');
		$model = JModel::getInstance('objects', 'AccessModel');
		$model->setState('list.section_value',	$this->getState('section_value'));
		$model->setState('list.object_type',	'aco');
		$model->setState('list.hidden',			'0');
		$model->setState('list.order',			's.order_value,a.section_value,a.order_value,a.name');
		if ($aclType = $this->getState('acl_type')) {
			$model->setState('list.where', 'a.acl_type = '.(int) $aclType);
		}
		return $model->getList();
	}

	function getAROGroups()
	{
		$model = JModel::getInstance('Groups', 'AccessModel');
		$model->setState('list.group_type',	'aro');
		$model->setState('list.tree',		'1');
		$model->setState('list.parent_id',	ACCESS_USERS_ARO_ID);
		$model->setState('list.order',		'a.lft');
		return $model->getList();
	}

	function getAXOs()
	{
		$model = JModel::getInstance('Objects', 'AccessModel');
		$model->setState('list.section_value',	$this->getState('section_value'));
		$model->setState('list.object_type',	'axo');
		$model->setState('list.hidden',			'0');
		$model->setState('list.order',			'a.order_value,a.name');
		return  $model->getList();
	}

	function getAXOGroups()
	{
		$model = JModel::getInstance('Groups', 'AccessModel');
		$model->setState('list.group_type',	'axo');
		$model->setState('list.tree',		'1');
		$model->setState('list.order',		'a.lft');
		$model->setState('list.parent_id',	1);
		return $model->getList();
	}

	function save($values)
	{
		$acl		= &JFactory::getACL();

		$acoArray		= JArrayHelper::getValue($values, 'aco_array', array(), 'array');
		$aroArray		= JArrayHelper::getValue($values, 'aro_array', array(), 'array');
		$aroGroupIds	= JArrayHelper::getValue($values, 'aro_group_ids', array(), 'array');
		$axoArray		= JArrayHelper::getValue($values, 'axo_array', array(), 'array');
		$axoGroupIds	= JArrayHelper::getValue($values, 'axo_group_ids', array(), 'array');

		$allow			= JArrayHelper::getValue($values, 'allow', 1, 'int');
		$enabled		= JArrayHelper::getValue($values, 'enabled', 1, 'int');
		$returnValue	= JArrayHelper::getValue($values, 'return_value');
		$note			= JArrayHelper::getValue($values, 'note');
		$sectionValue	= JArrayHelper::getValue($values, 'section_value');
		$aclId			= JArrayHelper::getValue($values, 'id', 0, 'int');
		$aclType		= JArrayHelper::getValue($values, 'acl_type', 1, 'int');

		//$acl->_debug = 1;
		$result = $acl->add_acl($acoArray, $aroArray, $aroGroupIds, $axoArray, $axoGroupIds, $allow, $enabled, $returnValue, $note, $sectionValue, $aclId, $aclType);

		if ($result) {
			$this->setState('id', $result);
		}
		else {
			$result = JError::raiseWarning(500, array_pop($acl->_debugLog));
		}
		return $result;
	}

	function delete($ids = array())
	{
		$acl		= &JFactory::getACL();
		foreach ((array) $ids as $id)
		{
			$result		= $acl->del_acl($id);
			$acl->_debug = 1;
			if ($result == false) {
				JError::raiseWarning(500, array_pop($acl->_debugLog));
				break;
			}
		}
		return $result;
	}

	function allow($ids = array(), $value = 1)
	{
		if (empty($ids)) {
			return JException('No items selected');
		}
		else
		{
			$acl	= &JFactory::getACL();
			$db		= $this->getDBO();
			JArrayHelper::toInteger($ids);

			$query	= 'UPDATE #__core_acl_acl' .
					' SET allow = '.(int)($value ? 1 : 0) .
					' WHERE id IN ('.implode(',', $ids).')';
			$db->setQuery($query);
			if (!$db->query()) {
				return new JExecption($db->getErrorMsg());
			}
			return true;
		}
	}

	function enable($ids = array(), $value = 1)
	{
		if (empty($ids)) {
			return JException('No items selected');
		}
		else
		{
			$acl	= &JFactory::getACL();
			$db		= $this->getDBO();
			JArrayHelper::toInteger($ids);

			$query	= 'UPDATE #__core_acl_acl' .
					' SET enabled = '.(int)($value ? 1 : 0) .
					' WHERE id IN ('.implode(',', $ids).')';
			$db->setQuery($query);
			if (!$db->query()) {
				return new JExecption($db->getErrorMsg());
			}
			return true;
		}
	}
}
