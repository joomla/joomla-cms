<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Categories Component Category Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since 1.5
 */
class CategoriesModelCategory extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context		= 'com_categories.item';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Category', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_categories.edit.category.id'))) {
			$pk = (int) JRequest::getInt('item_id');
		}
		$this->setState('category.id', $pk);

		if (!($parentId = $app->getUserState('com_categories.edit.category.parent_id'))) {
			$parentId = JRequest::getInt('parent_id');
		}
		$this->setState('category.parent_id', $parentId);

		if (!($extension = $app->getUserState('com_categories.edit.category.extension'))) {
			$extension = JRequest::getCmd('extension', 'com_content');
		}
		$this->setState('category.extension', $extension);
		$parts = explode('.',$extension);
		// extract the component name
		$this->setState('category.component', $parts[0]);
		// extract the optional section name
		$this->setState('category.section', (count($parts)>1)?$parts[1]:null);

		// Load the parameters.
		$params	= &JComponentHelper::getParams('com_categories');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a category.
	 *
	 * @param	integer	An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return	mixed	Category data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('category.id');

		// Get a level row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$table->load($pk);

		// Check for a table object error.
		if ($error = $table->getError())
		{
			$this->setError($error);
			$false = false;
			return $false;
		}

		// Prime required properties.
		if (empty($table->id))
		{
			$table->parent_id	= $this->getState('category.parent_id');
			$table->extension	= $this->getState('category.extension');
		}

		// Convert the params field to an array.
		$registry = new JRegistry();
		$registry->loadJSON($table->params);
		$table->params = $registry->toArray();

		// Convert the metadata field to an array.
		$registry = new JRegistry();
		$registry->loadJSON($table->metadata);
		$table->metadata = $registry->toArray();

		// Convert the result to a JObject
		$result = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		return $result;
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
		$app		= &JFactory::getApplication();
		$lang		= &JFactory::getLanguage();
		$extension	= $this->getState('category.extension');
		$component	= $this->getState('category.component');
		$section	= $this->getState('category.section');

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_categories.edit.category.data', array());

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_categories/models/forms');
		JForm::addFieldPath(JPATH_ADMINISTRATOR.'/components/com_categories/models/fields');
		$form = &JForm::getInstance('category', "com_categories.category.$extension", true, array('array'=>'jform'));
		// Check for an error.
		if (JError::isError($form))
		{
			$this->setError($form->getMessage());
			return false;
		}

		// Get the component form if it exists
		jimport('joomla.filesystem.path');
		$name = 'category' . ($section ? ('.'.$section):'');
		$path = JPath::clean(JPATH_ADMINISTRATOR."/components/$component/$name.xml");
		if (file_exists($path))
		{
			$lang->load($component);
			$form->load($path, true, false);

			// Check for an error.
			if (JError::isError($form)) {
				$this->setError($form->getMessage());
				return false;
			}
		}

		// Try to find the component helper.
		$eName	= str_replace('com_', '', $component);
		$path	= JPath::clean(JPATH_ADMINISTRATOR."/components/$component/helpers/category.php");
		if (file_exists($path))
		{
			require_once $path;
			$cName	= ucfirst($eName).ucfirst($section).'HelperCategory';
			if (class_exists($cName) && is_callable(array($cName, 'onPrepareForm')))
			{
				$lang->load($component);
				call_user_func_array(array($cName, 'onPrepareForm'), array(&$form));

				// Check for an error.
				if (JError::isError($form)) {
					$this->setError($form->getMessage());
					return false;
				}
			}
		}

		// Get the dispatcher.
		$dispatcher	= &JDispatcher::getInstance();

		// Load the plugin group.
		JPluginHelper::importPlugin('content');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareForm', array($form->getName(), $form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			// Convert to a JException if necessary.
			if (!JError::isError($error)) {
				$error = new JException($error, 500);
			}
			
			$this->setError($error);
			return false;
		}

		// Set the access control rules field component value.
		$form->setFieldAttribute('rules', 'component', $component);
		$form->setFieldAttribute('rules', 'section', $name);

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	$pk The numeric id of a row
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('category.id');

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
			if (!$table->checkin($pk)) {
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
	 *
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

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
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		$pk		= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('category.id');
		$isNew	= true;

		// Get a row instance.
		$table = &$this->getTable();

		// Load the row if saving an existing category.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Set the new parent id if set.
		if ($table->parent_id != $data['parent_id']) {
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Bind the rules.
		if (isset($data['rules']))
		{
			$rules = new JRules($data['rules']);
			$table->setRules($rules);
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

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id)) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('category.id', $table->id);

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
		$pks = (array) $pks;

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $pk)
		{
			// Delete the category (but keep the children)
			if (!$table->delete((int) $pk, false))
			{
				$this->setError($table->getError());
				return false;
			}			
		}

		return true;
	}

	/**
	 * Method to publish categories.
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	function publish($pks, $value = 1)
	{
		$pks = (array) $pks;

		// Get the current user object.
		$user = &JFactory::getUser();

		// Get an instance of the table row.
		$table = &$this->getTable();

		// Attempt to publish the items.
		if (!$table->publish($pks, $value, $user->get('id')))
		{
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
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('category.id');

		// If the ordering direction is 0 then we aren't moving anything.
		if ($direction == 0) {
			return true;
		}

		// Get a row instance.
		$table = &$this->getTable();

		// Move the row down in the ordering.
		if ($direction > 0)
		{
			if (!$table->orderDown($pk)) {
				$this->setError($table->getError());
				return false;
			}
		}

		// Move the row up in the ordering.
		else
		{
			if (!$table->orderUp($pk)) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function rebuild()
	{
		// Get an instance of the table obejct.
		$table = &$this->getTable();

		if (!$table->rebuild())
		{
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to perform batch operations on a category or a set of categories.
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

		if (!empty($commands['category_id']))
		{
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c' && !$this->_batchCopy($commands['category_id'], $pks)) {
				return false;
			}
			else if ($cmd == 'm' && !$this->_batchMove($commands['category_id'], $pks)) {
				return false;
			}
			$done = true;
		}

		if (!$done)
		{
			$this->setError('Categories_Error_Insufficient_batch_information');
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

	/**
	 * Batch move categories to a new parent.
	 *
	 * @param	int		The new category or sub-category.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function _batchMove($value, $pks)
	{
	}

	/**
	 * Batch copy categories to a new parent.
	 *
	 * @param	int		The new category or sub-category.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function _batchCopy($value, $pks)
	{
	}
}
