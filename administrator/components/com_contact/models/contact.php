<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Item Model for Contacts.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @version		1.6
 */
class ContactModelContact extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context		= 'com_contact.item';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 */
	public function getTable($type = 'Contact', $prefix = 'ContactTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app	= &JFactory::getApplication('administrator');
		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_contact.edit.contact.id'))) {
			$pk = (int) JRequest::getInt('item_id');
		}
		$this->setState('contact.id',			$pk);

		// Load the parameters.
		$params	= &JComponentHelper::getParams('com_contact');
		// Load the parameters.
		$this->setState('params', $params);
			}


	/**
	 * Method to get an item.
	 *
	 * @param	integer	The id of the  item to get.
	 *
	 * @return	mixed	Item data object on success, false on failure.
	 */
	public function &getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('contact.id');
		$false	= false;

		// Get a row instance.
		$table = &$this->getTable();
		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Prime required properties.
		if (empty($table->id))
		{
			$table->parent_id	= $this->getState('item.parent_id');
			//$table->menutype	= $this->getState('item.menutype');
			//$table->type		= $this->getState('item.type');
		}

		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadJSON($table->params);
		$table->params = $registry->toArray();

		// Convert the params field to an array.
		$registry = new JRegistry;
		//$registry->loadJSON($table->metadata);
		$table->metadata = $registry->toArray();


		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		return $value;
	}

	/**
	 * Method to get the row form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= &JFactory::getApplication();
		JImport('joomla.form.form');
		JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_users/models/fields');
		// Get the form.
		$form = parent::getForm('contact', 'com_contact.contact', array('array' => 'jform', 'event' => 'onPrepareForm'));
		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_contact.edit.contact.data', array());
		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = & JDispatcher::getInstance();
		$table		= &$this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('contact.id');
		$isNew		= true;

		// Include the contact plugins for the onSave events.
		JPluginHelper::importPlugin('contact');

		// Load the row if saving an existing item.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		// Load email_form params into params array
		foreach ($data['email_form'] as $key => $value) {
			$data['params'][$key] = $value;
		}
		$data['email_form'] = array();

		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
			}
		$result = $dispatcher->trigger('onBeforeContactSave', array(&$table, $isNew));
		if (in_array(false, $result, true)) {
			JError::raiseError(500, $row->getError());
			return false;
			}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = &JFactory::getCache('com_contact');
		$cache->clean();

		$dispatcher->trigger('onAfterContactSave', array(&$table, $isNew));
		return true;
	}
		/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete($pks)
	{
		$dispatcher = & JDispatcher::getInstance();
		// Include the content plugins for the onSave events.
		JPluginHelper::importPlugin('content');

		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $itemId)
		{
			$table->load($itemId); // get contact for onBeforeContacttDelete event
			$result = $dispatcher->trigger('onBeforeContacttDelete', array($table));
			if (in_array(false, $result, true))
			{
				JError::raiseError(500, $row->getError());
				return false;
			}

			// delete row
			if (!$table->delete($itemId))
			{
				$this->setError($table->getError());
				return false;
			}

			$dispatcher->trigger('onAfterContactDelete', array($itemId));
		}


		return true;
	}
	/**
	 * Method to publish
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	public function publish($pks, $value = 1)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Get the current user object.
		$user = &JFactory::getUser();

		// Get a category row instance.
		$table = &$this->getTable();

		// Attempt to publish the items.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}
	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		The numeric id of the row to move.
	 * @param	integer	Increment, usually +1 or -1
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function ordering($pk, $direction = 0)
	{
		// Sanitize the id and adjustment.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		// Get a row instance.
		$table = &$this->getTable();

		// Attempt to adjust the row ordering.
		if (!$table->ordering((int) $direction, $pk)) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}
	/**

	 * Method to checkin a row.
	 *
	 * @param	integer	$id		The numeric id of a row
	 * @return	boolean	True on success/false on failure
	 * @since	1.6
	 */

	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('contact.id');
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			$user	= &JFactory::getUser();

			// Get an instance of the row to checkin.
			$table = &$this->getTable();
			if (!$table->load($pk)) {
				$this->setError($table->getError());
				return false;
			}
			// Check if this is the user having previously checked out the row.
			if ($table->checked_out > 0 && $table->checked_out != $user->get('id')) {
				$this->setError(JText::_('JError_Checkin_user_mismatch'));
				return false;
			}

			// Attempt to check the row in.
			if (!$table->checkin($contactId)) {
				$this->setError($table->getError());
				return false;
			}
		}
		return true;
	}
	/**
	 * Method to check-out a row for editing.
	 *
	 * @param	int		$pk	The numeric id of the row to check-out.
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk		= (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			// Get a row instance.
			$table = &$this->getTable();

			// Get the current user object.
			$user = &JFactory::getUser();

			// Attempt to check the row out.
			if (!$table->checkout($user->get('id'), $pk)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to perform batch operations on a category or a set of contacts.
	 *
	 * @param	array	An array of commands to perform.
	 * @param	array	An array of category ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	function batch($commands, $pks)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true)) {
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks)) {
			$this->setError(JText::_('JError_No_items_selected'));
			return false;
		}

		$done = false;

		if (!empty($commands['assetgroup_id']))
		{
			if (!$this->_batchAccess($commands['assetgroup_id'], $pks)) {
				return false;
			}
			$done = true;
		}

		if (!empty($commands['menu_id']))
		{
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c' && !$this->_batchCopy($commands['menu_id'], $pks)) {
				return false;
			}
			else if ($cmd == 'm' && !$this->_batchMove($commands['menu_id'], $pks)) {
				return false;
			}
			$done = true;
		}

		if (!$done)
		{
			$this->setError('Menus_Error_Insufficient_batch_information');
			return false;
		}

		return true;
	}

	/**
	 * Batch access level changes for a group of rows.
	 *
	 * @param	int		The new value matching an Asset Group ID.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function _batchAccess($value, $pks)
	{
		$table = &$this->getTable();
		foreach ($pks as $pk)
		{
			$table->reset();
			$table->load($pk);
			$table->access = (int) $value;
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}


}
