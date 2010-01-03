<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Menu Item Model for Menus.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusModelMenu extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context		= 'com_menus.menu';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'MenuType', $prefix = 'JTable', $config = array())
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
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($id = (int)$app->getUserState('com_menus.edit.menu.id'))) {
			$id = (int)JRequest::getInt('item_id');
		}
		$this->setState('menu.id', $id);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_menus');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a menu item.
	 *
	 * @param	integer	The id of the menu item to get.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('menu.id');
		$false	= false;

		// Get a menu item row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->serError($table->getError());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		return $value;
	}

	/**
	 * Method to get the menu item form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app = &JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('menu', 'com_menus.menu', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_menus.edit.menu.data', array());

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
		$id	= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('menu.id');
		$isNew	= true;

		// Get a row instance.
		$table = &$this->getTable();

		// Load the row if saving an existing item.
		if ($id > 0) {
			$table->load($id);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$this->setState('menu.id', $table->id);

		return true;
	}

	/**
	 * Method to delete groups.
	 *
	 * @param	array	An array of item ids.
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete($itemIds)
	{
		// Sanitize the ids.
		$itemIds = (array) $itemIds;
		JArrayHelper::toInteger($itemIds);

		// Get a group row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($itemIds as $itemId) {
			// TODO: Delete the menu associations - Menu items and Modules

			if (!$table->delete($itemId))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return	array
	 */
	public function &getModules()
	{
		$db = &$this->getDbo();

		$db->setQuery(
			'SELECT id, title, params, position' .
			' FROM #__modules' .
			' WHERE module = '.$db->quote('mod_mainmenu')
		);
		$modules = $db->loadObjectList();

		$result = array();

		foreach ($modules as &$module) {
			if ($module->params[0] == '{') {
				$params = JArrayHelper::toObject(json_decode($module->params, true), 'JObject');
			} else {
				$params = new JParameter($module->params);
			}

			$menuType = $params->get('menutype');
			if (!isset($result[$menuType])) {
				$result[$menuType] = array();
			}
			$result[$menuType][] = &$module;
		}

		return $result;
	}
}
