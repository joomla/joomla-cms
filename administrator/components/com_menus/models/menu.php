<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_MENUS_MENU';

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context		= 'com_menus.menu';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 *
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.delete', 'com_menus.menu.'.(int) $record->id);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 *
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.edit.state', 'com_menus.menu.'.(int) $record->id);
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'MenuType', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$id = (int) JRequest::getInt('id');
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
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');
		return $value;
	}

	/**
	 * Method to get the menu item form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_menus.menu', 'menu', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
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
		$data = JFactory::getApplication()->getUserState('com_menus.edit.menu.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
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
		$table = $this->getTable();

		// Load the row if saving an existing item.
		if ($id > 0) {
			$table->load($id);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('menu.id', $table->id);

		// Clean the cache
		$this->cleanCache();

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
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($itemIds as $itemId) {
			// TODO: Delete the menu associations - Menu items and Modules

			if (!$table->delete($itemId))
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return	array
	 */
	public function &getModules()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);
		$query->from('#__modules as a');
		$query->select('a.id, a.title, a.params, a.position');
		$query->where('module = '.$db->quote('mod_menu'));
		$query->select('ag.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		$db->setQuery($query);

		$modules = $db->loadObjectList();

		$result = array();

		foreach ($modules as &$module) {
			$params = new JRegistry;
			$params->loadString($module->params);

			$menuType = $params->get('menutype');
			if (!isset($result[$menuType])) {
				$result[$menuType] = array();
			}
			$result[$menuType][] = &$module;
		}

		return $result;
	}

	/**
	 * Custom clean cache method
	 *
	 * @since	1.6
	 */
	protected function cleanCache($group = null, $client_id = 0) {
		parent::cleanCache('com_modules');
		parent::cleanCache('mod_menu');
	}
}
