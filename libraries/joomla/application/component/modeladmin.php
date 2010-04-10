<?php
/**
 * @version		$Id: module.php 15793 2010-04-02 17:11:34Z louis $
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Prototype admin model.
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
abstract class JModelAdmin extends JModelForm
{	
	protected $_msgprefix = null;
	
	protected $_option = null;
	
	protected $_item = null;
	
	protected $_context = null;
	
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->_msgprefix = strtoupper(str_replace('.', '_', $this->_context));
	}
	
	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	The ID of the primary key.
	 *
	 * @return	boolean
	 */
	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState($this->_item.'.id');

		return parent::checkin($pk);
	}

	/**
	 * Method to override check-out a row for editing.
	 *
	 * @param	int		The ID of the primary key.
	 * @return	boolean
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->_item.'.id');

		return parent::checkout($pk);
	}

	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				if ($table->catid) {
					$allow = $user->authorise('core.edit.state', 'com_newsfeeds.category.'.(int) $table->catid);
				}
				else {
					$allow = $user->authorise('core.edit.state', 'com_newsfeeds');
				}

				if ($allow)
				{
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
				}
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to publish records.
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	function publish(&$pks, $value = 1)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				$allow = $user->authorise('core.edit.state', $this->_option);

				if (!$allow) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		The ID of the primary key to move.
	 * @param	integer	Increment, usually +1 or -1
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function reorder($pks, $delta = 0)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;
		$result	= true;

		// Access checks.
		$allow = $user->authorise('core.edit', $this->_option);
		if (!$allow) {
			$this->setError(JText::_('JERROR_CORE_EDIT_NOT_PERMITTED'));
			return false;
		}

		foreach ($pks as $i => $pk) {
			$table->reset();
			if ($table->load($pk) && $this->checkout($pk)) {
				$where = array();
				$where = $this->_orderConditions($table);
				if (!$table->move($delta, $where)) {
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}
				$this->checkin($pk);
			} else {
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param	array	An array of primary key ids.
	 * @param	int		+/-1
	 */
	function saveorder($pks, $order)
	{
		// Initialise variables.
		$table		= $this->getTable();
		$conditions	= array();
		$user = JFactory::getUser();

		if (empty($pks)) {
			return JError::raiseWarning(500, JText::_($this->_msgprefix.'_ERROR_NO_ITEMS_SELECTED'));
		}

		// update ordering values
		foreach ($pks as $i => $pk) {
			$table->load((int) $pk);

			// Access checks.
			$allow = $user->authorise('core.edit.state', $this->_option);

			if (!$allow) {
				// Prune items that you can't change.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
			} else if ($table->ordering != $order[$i]) {
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				}
				// remember to reorder within position and client_id
				$condition = $this->_orderConditions($table);
				$found = false;
				foreach ($conditions as $cond) {
					if ($cond[1] == $condition) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$conditions[] = array ($table->id, $condition);
				}
			}
		}

		// Execute reorder for each category.
		foreach ($conditions as $cond) {
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$cache = JFactory::getCache($this->_option);
		$cache->clean();

		return true;
	}	
	
	abstract function _orderConditions($table = null);
}