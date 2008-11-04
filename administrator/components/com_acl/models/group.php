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

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessModelGroup extends AccessModelPrototypeItem
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
	function &getTable()
	{
		$type = $this->getState('group_type');
		return JTable::getInstance($type.'Group');
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
			$id = (int) $session->get('com_acl.group.id', $this->getState('id'));

			$table = $this->getTable();
			if (!$table->load($id)) {
				$this->setError($table->getError());
			}
			$this->_item = JArrayHelper::toObject($table->getProperties(1), 'JStdClass');
		}
		return $this->_item;
	}

	/**
	 * Save override
	 */
	function save($input)
	{
		$result	= true;
		$user	= &JFactory::getUser();
		$table	= &$this->getTable();
		$isNew	= empty($input['id']);

		if (!$table->save($input)) {
			$result	= JError::raiseWarning(500, $table->getError());
		}
		if (strtolower($this->getState('group_type')) == 'axo') {
			if ($isNew) {
				$table->value = $table->id;
				$table->store();
			}
		}
		// Set the new id (if new)
		$this->setState('id', $table->id);

		return $result;
	}
}