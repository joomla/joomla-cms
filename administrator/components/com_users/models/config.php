<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Configuration model for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @version		1.0
 */
class UsersModelConfig extends JModel
{
	/**
	 * Flag to indicate model state initialization.
	 *
	 * @access	protected
	 * @var		boolean
	 */
	var $__state_set		= null;

	/**
	 * Overridden method to get model state variables.
	 *
	 * @access	public
	 * @param	string	Optional parameter name.
	 * @return	object	The property where specified, the state object where omitted.
	 * @since	1.0
	 */
	function getState($property = null)
	{
		// Pre-populate the state if not yet done.
		if (!$this->__state_set)
		{
			// Load the component configuration.
			$this->setState('config', JComponentHelper::getParams('com_users'));

			$this->__state_set = true;
		}

		return parent::getState($property);
	}

	/**
	 * Method to save the component configuration
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function save()
	{
		// Get a component row object.
		$table = & JTable::getInstance('component');

		// Load the component data.
		if (!$table->loadByOption('com_users')) {
			$this->setError($table->getError());
			return false;
		}

		// Build the data array to bind.
		$data			= array();
		$data['option']	= 'com_users';
		$data['params']	= JRequest::getVar('params', array(), 'post', 'array');

		// Bind the new values to the component row.
		$table->bind($data);

		// Check for errors.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Attempt to store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to import the component configuration.
	 *
	 * @access	public
	 * @param	string	The configuration string in INI format.
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function import($data = null)
	{
		// Load the component configuration fields.
		$table = &JTable::getInstance('component');
		if (!$table->loadByOption('com_users')) {
			$this->setError($table->getError());
			return false;
		}

		// Set the new configuration values.
		$table->set('params', $data);

		// Check for errors.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Attempt to store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}
}