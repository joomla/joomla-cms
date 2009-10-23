<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.access.helper');

/**
 * Access Level model for Users.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersModelLevel extends JModelForm
{
	/**
	 * Array of items for memory caching.
	 *
	 * @var		array
	 */
	protected $_items = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app		= JFactory::getApplication('administrator');
		$params		= &JComponentHelper::getParams('com_users');

		// Load the level state.
		if (!$levelId = (int)$app->getUserState('com_users.edit.level.id')) {
			$levelId = (int)JRequest::getInt('level_id');
		}
		$this->setState('level.id', $levelId);

		// Add the level id to the context to preserve sanity.
		$context = 'com_users.level.'.$levelId.'.';

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a level item.
	 *
	 * @param	integer	The id of the level to get.
	 * @return	mixed	Group data object on success, false on failure.
	 */
	public function &getItem($levelId = null)
	{
		// Initialize variables.
		$levelId = (!empty($levelId)) ? $levelId : (int)$this->getState('level.id');
		$false	= false;

		// Get a level row instance.
		$table = &$this->getTable('Viewlevels', 'JTable');

		// Attempt to load the row.
		$return = $table->load($levelId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->serError($table->getError());
			return $false;
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		$value->groups = json_decode($value->rules);

		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialize variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('level', 'com_users.level', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_users.edit.level.data', array());

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
	 */
	public function save($data)
	{
		// Initialize variables.
		$levelId = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('level.id');

		// Are we dealing with a new access level?
		if ($levelId) {
			$isNew = false;
		} else {
			$isNew = true;
		}

		if ($isNew)
		{
			$table = JTable::getInstance('Viewlevels');
			$table->title = $data['title'];
			$table->rules = json_encode($data['groups']);

			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			$this->setState('level.id', $table->id);
		}
		else
		{
			// Update the data as necessary and store the access level.
			$item = & JTable::getInstance('Viewlevels');
			$item->load($data['id']);

			$item->title = $data['title'];
			$item->rules = json_encode($data['groups']);

			// Store the access level.
			if (!$item->store()) {
				$this->setError($item->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to delete levels.
	 *
	 * @param	array	An array of level ids.
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete($levelIds)
	{
		// Sanitize the ids.
		$levelIds = (array) $levelIds;
		JArrayHelper::toInteger($levelIds);

		// Get a database object.
		$db = &$this->getDbo();

		$db->setQuery(
			'DELETE FROM `#__viewlevels`' .
			' WHERE `id` IN ('.implode(',', $levelIds).')'
		);

		$db->Query();

		return true;
	}
}
