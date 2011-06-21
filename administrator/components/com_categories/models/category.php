<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Categories Component Category Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesModelCategory extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_CATEGORIES';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id)) {
			if ($record->published != -2) {
				return ;
			}
			$user = JFactory::getUser();

			return $user->authorise('core.delete', $record->extension.'.category.'.(int) $record->id);

		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing category.
		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', $record->extension.'.category.'.(int) $record->id);
		}
		// New category, so check against the parent.
		else if (!empty($record->parent_id)) {
			return $user->authorise('core.edit.state', $record->extension.'.category.'.(int) $record->parent_id);
		}
		// Default to component settings if neither category nor parent known.
		else {
			return $user->authorise('core.edit.state', $record->extension);
		}
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	*/
	public function getTable($type = 'Category', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		$parentId = JRequest::getInt('parent_id');
		$this->setState('category.parent_id', $parentId);

		// Load the User state.
		$pk = (int) JRequest::getInt('id');
		$this->setState($this->getName().'.id', $pk);

		$extension = JRequest::getCmd('extension', 'com_content');
		$this->setState('category.extension', $extension);
		$parts = explode('.',$extension);

		// Extract the component name
		$this->setState('category.component', $parts[0]);

		// Extract the optional section name
		$this->setState('category.section', (count($parts)>1)?$parts[1]:null);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_categories');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a category.
	 *
	 * @param	integer	An optional id of the object to get, otherwise the id from the model state is used.
	 * @return	mixed	Category data object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk)) {

			// Prime required properties.
			if (empty($result->id)) {
				$result->parent_id	= $this->getState('category.parent_id');
				$result->extension	= $this->getState('category.extension');
			}

			// Convert the metadata field to an array.
			$registry = new JRegistry();
			$registry->loadString($result->metadata);
			$result->metadata = $registry->toArray();

			// Convert the created and modified dates to local user time for display in the form.
			jimport('joomla.utilities.date');
			$tz	= new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if (intval($result->created_time)) {
				$date = new JDate($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toMySQL(true);
			}
			else {
				$result->created_time = null;
			}

			if (intval($result->modified_time)) {
				$date = new JDate($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toMySQL(true);
			}
			else {
				$result->modified_time = null;
			}
		}

		return $result;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$extension	= $this->getState('category.extension');

		// A workaround to get the extension into the model for save requests.
		if (empty($extension) && isset($data['extension'])) {
			$extension	= $data['extension'];
			$parts		= explode('.',$extension);

			$this->setState('category.extension',	$extension);
			$this->setState('category.component',	$parts[0]);
			$this->setState('category.section',		@$parts[1]);
		}

		// Get the form.
		$form = $this->loadForm('com_categories.category'.$extension, 'category', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Modify the form based on Edit State access controls.
		if (empty($data['extension'])) {
			$data['extension'] = $extension;
		}


		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * A protected method to get the where clause for the reorder
	 * This ensures that the row will be moved relative to a row with the same extension
	 *
	 * @param	JCategoryTable	current table instance
	 *
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table)
	{
		return 'extension = ' . $this->_db->Quote($table->extension);
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
		$data = JFactory::getApplication()->getUserState('com_categories.edit.'.$this->getName().'.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * @param	object	A form object.
	 * @param	mixed	The data expected for the form.
	 * @throws	Exception if there is an error loading the form.
	 * @since	1.6
	 */
	protected function preprocessForm(JForm $form, $data, $groups = '')
	{
		jimport('joomla.filesystem.path');

		// Initialise variables.
		$lang		= JFactory::getLanguage();
		$extension	= $this->getState('category.extension');
		$component	= $this->getState('category.component');
		$section	= $this->getState('category.section');

		// Get the component form if it exists
		jimport('joomla.filesystem.path');
		$name = 'category'.($section ? ('.'.$section):'');

		// Looking first in the component models/forms folder
		$path = JPath::clean(JPATH_ADMINISTRATOR."/components/$component/models/forms/$name.xml");

		// Old way: looking in the component folder
		if (!file_exists($path)) {
			$path = JPath::clean(JPATH_ADMINISTRATOR."/components/$component/$name.xml");
		}

		if (file_exists($path)) {
			$lang->load($component, JPATH_BASE, null, false, false);
			$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false);

			if (!$form->loadFile($path, false)) {
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Try to find the component helper.
		$eName	= str_replace('com_', '', $component);
		$path	= JPath::clean(JPATH_ADMINISTRATOR."/components/$component/helpers/category.php");

		if (file_exists($path)) {
			require_once $path;
			$cName	= ucfirst($eName).ucfirst($section).'HelperCategory';

			if (class_exists($cName) && is_callable(array($cName, 'onPrepareForm'))) {
					$lang->load($component, JPATH_BASE, null, false, false)
				||	$lang->load($component, JPATH_BASE . '/components/' . $component, null, false, false)
				||	$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
				||	$lang->load($component, JPATH_BASE . '/components/' . $component, $lang->getDefault(), false, false);
				call_user_func_array(array($cName, 'onPrepareForm'), array(&$form));

				// Check for an error.
				if (JError::isError($form)) {
					$this->setError($form->getMessage());
					return false;
				}
			}
		}

		// Set the access control rules field component value.
		$form->setFieldAttribute('rules', 'component',	$component);
		$form->setFieldAttribute('rules', 'section',	$name);

		// Trigger the default form events.
		parent::preprocessForm($form, $data);
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
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Load the row if saving an existing category.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if (JRequest::getVar('task') == 'save2copy') {
			list($title,$alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
			$data['title']	= $title;
			$data['alias']	= $alias;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Bind the rules.
		if (isset($data['rules'])) {
			$rules = new JRules($data['rules']);
			$table->setRules($rules);
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$this->name, &$table, $isNew));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, &$table, $isNew));

		// Rebuild the path for the category:
		if (!$table->rebuildPath($table->id)) {
			$this->setError($table->getError());
			return false;
		}

		// Rebuild the paths of the category's children:
		if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState($this->getName().'.id', $table->id);

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return	boolean	False on failure or error, true otherwise.
	 * @since	1.6
	 */
	public function rebuild()
	{
		// Get an instance of the table obejct.
		$table = $this->getTable();

		if (!$table->rebuild()) {
			$this->setError($table->getError());
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the reordered nested set tree.
	 * First we save the new order values in the lft values of the changed ids.
	 * Then we invoke the table rebuild to implement the new ordering.
	 *
	 * @return	boolean false on failuer or error, true otherwise
	 * @since	1.6
	*/
	public function saveorder($idArray = null, $lft_array = null)
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table->saveorder($idArray, $lft_array)) {
			$this->setError($table->getError());
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;

	}

	/**
	 * Batch access level changes for a group of rows.
	 *
	 * @param	int		The new value matching an Asset Group ID.
	 * @param	array	An array of row IDs.
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 * @since	1.6
	 */
	protected function batchAccess($value, $pks)
	{
		// Check that user has edit permission for every category being changed
		// Note that the entire batch operation fails if any category lacks edit permission
		$user	= JFactory::getUser();
		$extension = JRequest::getWord('extension');
		foreach ($pks as $pk) {
			if (!$user->authorise('core.edit', $extension.'.category.'.$pk)) {
				// Error since user cannot edit this category
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_EDIT'));
				return false;
			}
		}
		$table = $this->getTable();
		foreach ($pks as $pk) {
			$table->reset();
			$table->load($pk);
			$table->access = (int) $value;
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Batch copy categories to a new category.
	 *
	 * @param	int		$value	The new category or sub-item.
	 * @param	array	$pks	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 * @since	1.6
	 */
	protected function batchCopy($value, $pks)
	{
		// $value comes as {parent_id}.{extension}
		$parts		= explode('.', $value);
		$parentId	= (int) JArrayHelper::getValue($parts, 0, 1);

		$table	= $this->getTable();
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$extension = JRequest::getWord('extension');

		// Check that the parent exists
		if ($parentId) {
			if (!$table->load($parentId)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Non-fatal error
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
			// Check that user has create permission for parent category
			$canCreate = ($parentId == $table->getRootId()) ? $user->authorise('core.create', $extension) :
				$user->authorise('core.create', $extension.'.category.'.$parentId);
			if (!$canCreate) {
				// Error since user cannot create in parent category
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
				return false;
			}
		}

		// If the parent is 0, set it to the ID of the root item in the tree
		if (empty($parentId)) {
			if (!$parentId = $table->getRootId()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			// Make sure we can create in root
			elseif (!$user->authorise('core.create', $extension)) {
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
				return false;
			}
		}

		// We need to log the parent ID
		$parents = array();

		// Calculate the emergency stop count as a precaution against a runaway loop bug
		$db->setQuery(
			'SELECT COUNT(id)' .
			' FROM #__categories'
		);
		$count = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks) && $count > 0)
		{
			// Pop the first id off the stack
			$pk = array_shift($pks);

			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Copy is a bit tricky, because we also need to copy the children
			$db->setQuery(
				'SELECT id' .
				' FROM #__categories' .
				' WHERE lft > '.(int) $table->lft.' AND rgt < '.(int) $table->rgt
			);
			$childIds = $db->loadResultArray();

			// Add child ID's to the array only if they aren't already there.
			foreach ($childIds as $childId)
			{
				if (!in_array($childId, $pks)) {
					array_push($pks, $childId);
				}
			}

			// Make a copy of the old ID and Parent ID
			$oldId				= $table->id;
			$oldParentId		= $table->parent_id;

			// Reset the id because we are making a copy.
			$table->id			= 0;

			// If we a copying children, the Old ID will turn up in the parents list
			// otherwise it's a new top level item
			$table->parent_id	= isset($parents[$oldParentId]) ? $parents[$oldParentId] : $parentId;

			// Set the new location in the tree for the node.
			$table->setLocation($table->parent_id, 'last-child');

			// TODO: Deal with ordering?
			//$table->ordering	= 1;
			$table->level		= null;
			$table->asset_id	= null;
			$table->lft			= null;
			$table->rgt			= null;

			// Alter the title & alias
			list($title,$alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
			$table->title   = $title;
			$table->alias   = $alias;

			// Store the row.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			// Now we log the old 'parent' to the new 'parent'
			$parents[$oldId] = $table->id;
			$count--;
		}

		// Rebuild the hierarchy.
		if (!$table->rebuild()) {
			$this->setError($table->getError());
			return false;
		}

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id)) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Batch move categories to a new category.
	 *
	 * @param	int		$value	The new category or sub-item.
	 * @param	array	$pks	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 * @since	1.6
	 */
	protected function batchMove($value, $pks)
	{
		$parentId	= (int) $value;

		$table	= $this->getTable();
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$extension = JRequest::getWord('extension');

		// Check that the parent exists.
		if ($parentId) {
			if (!$table->load($parentId)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);

					return false;
				}
				else {
					// Non-fatal error
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
			// Check that user has create permission for parent category
			$canCreate = ($parentId == $table->getRootId()) ? $user->authorise('core.create', $extension) :
				$user->authorise('core.create', $extension.'.category.'.$parentId);
			if (!$canCreate) {
				// Error since user cannot create in parent category
				$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
				return false;
			}

			// Check that user has edit permission for every category being moved
			// Note that the entire batch operation fails if any category lacks edit permission
			foreach ($pks as $pk) {
				if (!$user->authorise('core.edit', $extension.'.category.'.$pk)) {
					// Error since user cannot edit this category
					$this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_EDIT'));
					return false;
				}
			}
		}


		// We are going to store all the children and just move the category
		$children = array();

		// Parent exists so we let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new location in the tree for the node.
			$table->setLocation($parentId, 'last-child');

			// Check if we are moving to a different parent
			if ($parentId != $table->parent_id) {
				// Add the child node ids to the children array.
				$db->setQuery(
					'SELECT `id`' .
					' FROM `#__categories`' .
					' WHERE `lft` BETWEEN '.(int) $table->lft.' AND '.(int) $table->rgt
				);
				$children = array_merge($children, (array) $db->loadResultArray());
			}

			// Store the row.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			// Rebuild the tree path.
			if (!$table->rebuildPath()) {
				$this->setError($table->getError());
				return false;
			}
		}

		// Process the child rows
		if (!empty($children)) {
			// Remove any duplicates and sanitize ids.
			$children = array_unique($children);
			JArrayHelper::toInteger($children);

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Custom clean the cache of com_content and content modules
	 *
	 * @since	1.6
	 */
	protected function cleanCache()
	{
		$extension = JRequest::getCmd('extension');
		switch ($extension)
		{
			case 'com_content':
				parent::cleanCache('com_content');
				parent::cleanCache('mod_articles_archive');
				parent::cleanCache('mod_articles_categories');
				parent::cleanCache('mod_articles_category');
				parent::cleanCache('mod_articles_latest');
				parent::cleanCache('mod_articles_news');
				parent::cleanCache('mod_articles_popular');
				break;
			default:
				parent::cleanCache($extension);
				break;
		}
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param	int     The value of the parent category ID.
	 * @param   sting   The value of the category alias.
	 * @param   sting   The value of the category title.
	 *
	 * @return	array   Contains title and alias.
	 * @since	1.7
	 */
	function generateNewTitle(&$parent_id, &$alias, &$title)
	{
		// Alter the title & alias
		$catTable = JTable::getInstance('Category', 'JTable');
		while ($catTable->load(array('alias'=>$alias, 'parent_id'=>$parent_id))) {
			$m = null;
			if (preg_match('#-(\d+)$#', $alias, $m)) {
				$alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
			} else {
				$alias .= '-2';
			}
			if (preg_match('#\((\d+)\)$#', $title, $m)) {
				$title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
			} else {
				$title .= ' (2)';
			}
		}

		return array($title, $alias);
	}
}
