<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Menu Item Model for Menus.
 *
 * @since  1.6
 */
class MenusModelMenu extends JModelForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MENUS_MENU';

	/**
	 * Model context string.
	 *
	 * @var  string
	 */
	protected $_context = 'com_menus.menu';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		return JFactory::getUser()->authorise('core.delete', 'com_menus.menu.' . (int) $record->id);
	}

	/**
	 * Method to test whether the state of a record can be edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.edit.state', 'com_menus.menu.' . (int) $record->id);
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'MenuType', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$id = $app->input->getInt('id');
		$this->setState('menu.id', $id);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_menus');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a menu item.
	 *
	 * @param   integer  $itemId  The id of the menu item to get.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function &getItem($itemId = null)
	{
		$itemId = (!empty($itemId)) ? $itemId : (int) $this->getState('menu.id');

		// Get a menu item row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());

			return false;
		}

		$properties = $table->getProperties(1);
		$value      = ArrayHelper::toObject($properties, 'JObject');

		return $value;
	}

	/**
	 * Method to get the menu item form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_menus.menu', 'menu', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_menus.edit.menu.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}
		else
		{
			unset($data['preset']);
		}

		$this->preprocessData('com_menus.menu', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$id         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('menu.id');
		$isNew      = true;

		// Get a row instance.
		$table = $this->getTable();

		// Include the plugins for the save events.
		JPluginHelper::importPlugin('content');

		// Load the row if saving an existing item.
		if ($id > 0)
		{
			$isNew = false;
			$table->load($id);
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the before event.
		$result = $dispatcher->trigger('onContentBeforeSave', array($this->_context, &$table, $isNew));

		// Store the data.
		if (in_array(false, $result, true) || !$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the after save event.
		$dispatcher->trigger('onContentAfterSave', array($this->_context, &$table, $isNew));

		$this->setState('menu.id', $table->id);

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to delete groups.
	 *
	 * @param   array  $itemIds  An array of item ids.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function delete($itemIds)
	{
		$dispatcher = JEventDispatcher::getInstance();

		// Sanitize the ids.
		$itemIds = ArrayHelper::toInteger((array) $itemIds);

		// Get a group row instance.
		$table = $this->getTable();

		// Include the plugins for the delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($itemIds as $itemId)
		{
			if ($table->load($itemId))
			{
				// Trigger the before delete event.
				$result = $dispatcher->trigger('onContentBeforeDelete', array($this->_context, $table));

				if (in_array(false, $result, true) || !$table->delete($itemId))
				{
					$this->setError($table->getError());

					return false;
				}

				// Trigger the after delete event.
				$dispatcher->trigger('onContentAfterDelete', array($this->_context, $table));

				// TODO: Delete the menu associations - Menu items and Modules
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function &getModules()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->from('#__modules as a')
			->select('a.id, a.title, a.params, a.position')
			->where('module = ' . $db->quote('mod_menu'))
			->select('ag.title AS access_title')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		$db->setQuery($query);

		$modules = $db->loadObjectList();

		$result = array();

		foreach ($modules as &$module)
		{
			$params = new Registry($module->params);

			$menuType = $params->get('menutype');

			if (!isset($result[$menuType]))
			{
				$result[$menuType] = array();
			}

			$result[$menuType][] = & $module;
		}

		return $result;
	}

	/**
	 * Custom clean the cache
	 *
	 * @param   string   $group      Cache group name.
	 * @param   integer  $client_id  Application client id.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_menus', 0);
		parent::cleanCache('com_modules');
		parent::cleanCache('mod_menu', 0);
		parent::cleanCache('mod_menu', 1);
	}
}
