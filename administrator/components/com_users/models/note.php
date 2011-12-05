<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Category model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		2.5.0
 */
class UsersModelNote extends JModelAdmin
{
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_users.note', 'note', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	$pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		// Get the dispatcher and load the users plugins.
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array('com_users.note', $result));

		return $result;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param	string	$name		The table name. Optional.
	 * @param	string	$prefix		The class prefix. Optional.
	 * @param	array	$options	Configuration array for model. Optional.
	 *
	 * @return	object	The table
	 */
	public function getTable($name = 'Note', $prefix = 'UsersTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_users.edit.note.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('note.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('catid', JRequest::getInt('catid', $app->getUserState('com_users.notes.filter.category_id')));
			}
		}

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.1
	 */
	protected function populateState()
	{
		parent::populateState();

		$userId = JRequest::getInt('u_id');
		$this->setState('note.user_id', $userId);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 *
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		// Initialise variables.
		$pk		= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('note.id');
		$table	= $this->getTable();
		$isNew	= empty($pk);

		if (!$table->bind($data)) {
			$this->setError($table->getError());

			return false;
		}

		// JTableCategory doesn't bind the params, so we need to do that by hand.
		if (isset($data['params']) && is_array($data['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($data['params']);
			$table->params = $registry->toString();
			// This will give us INI format.
		}

		if (!$table->check()) {
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store()) {
			$this->setError($table->getError());

			return false;
		}

		$this->setState('note.id', $table->id);

		return true;
	}
}