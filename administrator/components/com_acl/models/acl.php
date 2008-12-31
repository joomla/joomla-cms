<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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
			$this->setState('acl_type',			JRequest::getInt('type', 1));
			$this->setState('section_value',	JRequest::getVar('section', 'core'));
			$this->__state_set = true;
		}
		return parent::getState($key, $default);
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
				// @todo Cannot tell if JTable::load throws an error on a null return
				//$this->setError($table->getError());
			}
			$this->_item = JArrayHelper::toObject($table->getProperties(1), 'JStdClass');

			if ($this->_item->id)
			{
				// Load the references into the item object
				if ($references = &$table->findReferences()) {
					$this->_item->references = &$references;
				}
				else {
					$this->setError($table->getError());
				}
				$this->setState('acl_type', $this->_item->acl_type);
			}
			else {
				jimport('joomla.acl.aclreferences');
				$this->_item->references = new JAclReferences;
				$this->_item->acl_type = $this->getState('acl_type');
				$this->_item->section_value = $this->getState('section_value');
			}
		}
		return $this->_item;
	}

	function getSections()
	{
		$model = JModel::getInstance('Section', 'AccessModel', array('ignore_request' => true));
		$model->setState('list.select',			'a.value, a.name AS text');
		$model->setState('list.section_type',	'acl');
		$model->setState('list.order',			'a.order_value,a.name');
		return $model->getList();
	}

	function getACOs()
	{
		//Model::addIncludePath(JPATH_COMPONENT.DS.'models');
		$aclType = $this->getState('acl_type', 1);

		$model = JModel::getInstance('objects', 'AccessModel', array('ignore_request' => true));
		$model->setState('list.object_type',	'aco');
		$model->setState('list.acl_type',		$aclType);
		$model->setState('list.hidden',			'0');
		$model->setState('list.order',			's.order_value,a.section_value,a.order_value,a.name');
		if ($aclType > 1) {
			$model->setState('list.section_value',	$this->getState('section_value'));
			$model->setState('list.where',			'a.acl_type = '.(int) $aclType);
		}
		return $model->getList();
	}

	function getAROGroups()
	{
		$model = JModel::getInstance('Groups', 'AccessModel', array('ignore_request' => true));
		$model->setState('list.group_type',	'aro');
		$model->setState('list.tree',		'1');
		$model->setState('list.parent_id',	ACCESS_USERS_ARO_ID);
		$model->setState('list.order',		'a.lft');
		return $model->getList();
	}

	function getAXOs()
	{
		$model = JModel::getInstance('Objects', 'AccessModel', array('ignore_request' => true));
		$model->setState('list.section_value',	$this->getState('section_value'));
		$model->setState('list.object_type',	'axo');
		$model->setState('list.hidden',			'0');
		$model->setState('list.order',			'a.order_value,a.name');
		return  $model->getList();
	}

	function getAXOGroups()
	{
		$model = JModel::getInstance('Groups', 'AccessModel', array('ignore_request' => true));
		$model->setState('list.group_type',	'axo');
		$model->setState('list.tree',		'1');
		$model->setState('list.order',		'a.lft');
		$model->setState('list.parent_id',	1);
		return $model->getList();
	}

	function save($values)
	{
		$table = &$this->getTable();

		if (!$table->bind($values)) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		jimport('joomla.acl.aclreferences');

		$references = new JAclReferences;

		if (!$references->bind($values)) {
			$this->setError($table->getError());
			return false;
		}

		if (!$table->updateReferences($references)) {
			$this->setError($table->getError());
			return false;
		}

		// Set the new id (if new)
		$this->setState('id', $table->id);

		return $result;
	}

	function delete($ids = array())
	{
		$table = JTable::getInstance('Acl', 'MembersTable');
		foreach ((array) $ids as $id)
		{
			if (!$table->delete($id)) {
				$this->setError($table->getError());
				return false;
			}
		}
		return true;
	}

	function allow($ids = array(), $value = 1)
	{
		if (empty($ids)) {
			$this->setError(JText::_('No items selected'));
			return false;
		}

		$acl	= &JFactory::getACL();
		$db		= $this->getDBO();
		JArrayHelper::toInteger($ids);

		$query	= 'UPDATE #__core_acl_acl' .
				' SET allow = '.(int)($value ? 1 : 0) .
				' WHERE id IN ('.implode(',', $ids).')';
		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}

	function enable($ids = array(), $value = 1)
	{
		if (empty($ids)) {
			$this->setError(JText::_('No items selected'));
			return false;
		}
		$acl	= &JFactory::getACL();
		$db		= $this->getDBO();
		JArrayHelper::toInteger($ids);

		$query	= 'UPDATE #__core_acl_acl' .
				' SET enabled = '.(int)($value ? 1 : 0) .
				' WHERE id IN ('.implode(',', $ids).')';
		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}
}
