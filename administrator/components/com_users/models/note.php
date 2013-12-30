<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User note model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @since       2.5
 */
class UsersModelNote extends JModelAdmin
{

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_users.note';

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   2.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.note', 'note', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   2.5
	 */
	public function getItem($pk = null)
	{
		$result = parent::getItem($pk);

		// Get the dispatcher and load the users plugins.
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the data preparation event.
		$dispatcher->trigger('onContentPrepareData', array('com_users.note', $result));

		return $result;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  The table object
	 *
	 * @since   2.5
	 */
	public function getTable($name = 'Note', $prefix = 'UsersTable', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Get the application
		$app = JFactory::getApplication();

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_users.edit.note.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('note.id') == 0)
			{
				$data->set('catid', $app->input->get('catid', $app->getUserState('com_users.notes.filter.category_id'), 'int'));
			}

			$userId = $app->input->get('u_id', 0, 'int');

			if ($userId != 0)
			{
				$data->user_id = $userId;
			}
		}

		$this->preprocessData('com_users.note', $data);

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState()
	{
		parent::populateState();

		$userId = JFactory::getApplication()->input->get('u_id', 0, 'int');
		$this->setState('note.user_id', $userId);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	/*public function save($data)
	{
		$pk		= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('note.id');
		$table	= $this->getTable();
		$isNew	= empty($pk);

		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// JTableCategory doesn't bind the params, so we need to do that by hand.
		if (isset($data['params']) && is_array($data['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($data['params']);
			$table->params = $registry->toString();
			// This will give us INI format.
		}

		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState('note.id', $table->id);

		return true;
	}*/
}
