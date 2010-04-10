<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Include dependancies.
jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT.'/helpers/menus.php';

/**
 * Menu Item Model for Menus.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusModelItem extends JModelAdmin
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context	= 'com_menus.item';

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->_item = 'item';
		$this->_option = 'com_menus';
	}
	
	/**
	 * Auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_menus.edit.item.id'))) {
			$pk = (int) JRequest::getInt('item_id');
		}
		$this->setState('item.id', $pk);

		if (!($parentId = $app->getUserState('com_menus.edit.item.parent_id'))) {
			$parentId = JRequest::getInt('parent_id');
		}
		$this->setState('item.parent_id', $parentId);

		if (!($menuType = $app->getUserState('com_menus.edit.item.menutype'))) {
			$menuType = JRequest::getCmd('menutype', 'mainmenu');
		}
		$this->setState('item.menutype', $menuType);

		if (!($type = $app->getUserState('com_menus.edit.item.type'))){
			$type = JRequest::getCmd('type');
		}
		$this->setState('item.type', $type);

		if ($link = $app->getUserState('com_menus.edit.item.link')) {
			$this->setState('item.link', $link);
		}

		// Load the parameters.
		$params	= &JComponentHelper::getParams('com_menus');
		$this->setState('params', $params);
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
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
			$this->setError(JText::_('COM_MENUS_NO_MENUITEMS_SELECTED'));
			return false;
		}

		$done = false;

		if (!empty($commands['assetgroup_id'])) {
			if (!$this->batchAccess($commands['assetgroup_id'], $pks)) {
				return false;
			}
			$done = true;
		}

		if (!empty($commands['menu_id'])) {
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c' && !$this->batchCopy($commands['menu_id'], $pks)) {
				return false;
			} else if ($cmd == 'm' && !$this->batchMove($commands['menu_id'], $pks)) {
				return false;
			}
			$done = true;
		}

		if (!$done) {
			$this->setError('COM_MENUS_ERROR_INSUFFICIENT_BATCH_INFORMATION');
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
	protected function batchAccess($value, $pks)
	{
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
	 * Batch copy menu items to a new menu or parent.
	 *
	 * @param	int		The new menu or sub-item.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function batchCopy($value, $pks)
	{
		// $value comes as {menutype}.{parent_id}
		$parts		= explode('.', $value);
		$menuType	= $parts[0];
		$parentId	= (int) JArrayHelper::getValue($parts, 1, 0);

		$table	= $this->getTable();
		$db		= $this->getDbo();

		// Check that the parent exists
		if ($parentId) {
			if (!$table->load($parentId)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Non-fatal error
					$this->setError(JText::_('COM_MENUS_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
		}

		// If the parent is 0, set it to the ID of the root item in the tree
		if (empty($parentId)) {
			if (!$parentId = $table->getRootId()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		// We need to log the parent ID
		$parents = array();

		// Calculate the emergency stop count as a precaution against a runaway loop bug
		$db->setQuery(
			'SELECT COUNT(id)' .
			' FROM #__menu'
		);
		$count = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks) && $count > 0) {
			// Pop the first id off the stack
			$pk = array_shift($pks);

			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Not fatal error
					$this->setError(JText::sprintf('COM_MENUS_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Copy is a bit tricky, because we also need to copy the children
			$db->setQuery(
				'SELECT id' .
				' FROM #__menu' .
				' WHERE lft > '.(int) $table->lft.' AND rgt < '.(int) $table->rgt
			);
			$childIds = $db->loadResultArray();

			// Add child ID's to the array only if they aren't already there.
			foreach ($childIds as $childId) {
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
			$table->menutype	= $menuType;
			// TODO: Deal with ordering?
			//$table->ordering	= 1;
			$table->level		= null;
			$table->lft		= null;
			$table->rgt	= null;

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
	 * Batch move menu items to a new menu or parent.
	 *
	 * @param	int		The new menu or sub-item.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function batchMove($value, $pks)
	{
		// $value comes as {menutype}.{parent_id}
		$parts		= explode('.', $value);
		$menuType	= $parts[0];
		$parentId	= (int) JArrayHelper::getValue($parts, 1, 0);

		$table	= &$this->getTable();
		$db		= &$this->getDbo();

		// Check that the parent exists.
		if ($parentId) {
			if (!$table->load($parentId)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Non-fatal error
					$this->setError(JText::_('COM_MENUS_BATCH_MOVE_PARENT_NOT_FOUND'));
					$parentId = 0;
				}
			}
		}

		// We are going to store all the children and just moved the menutype
		$children = array();

		// Parent exists so we let's proceed
		foreach ($pks as $pk) {
			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Not fatal error
					$this->setError(JText::sprintf('COM_MENUS_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new location in the tree for the node.
			$table->setLocation($parentId, 'last-child');

			// Check if we are moving to a different menu
			if ($menuType != $table->menutype) {
				// Add the child node ids to the children array.
				$db->setQuery(
					'SELECT `id`' .
					' FROM `#__menu`' .
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

			// Update the menutype field in all nodes where necessary.
			$db->setQuery(
				'UPDATE `#__menu`' .
				' SET `menutype` = '.$db->quote($menuType).
				' WHERE `id` IN ('.implode(',', $children).')'
			);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param	array		An optional array of source data.
	 *
	 * @return	mixed		JForm object on success, false on failure.
	 */
	public function getForm($data = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// The folder and element vars are passed when saving the form.
		if (empty($data)) {
			$item		= $this->getItem();
			$this->setState('item.link', $item->link);
			// The type should already be set.
		} else {
			$this->setState('item.link', JArrayHelper::getValue($data, 'link'));
			$this->setState('item.type', JArrayHelper::getValue($data, 'type'));
		}

		// Get the form.
		try {
			$form = parent::getForm('com_menus.item', 'item', array('control' => 'jform'), true);
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_menus.edit.item.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get a menu item.
	 *
	 * @param	integer	An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('item.id');

		// Get a level row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$table->load($pk);

		// Check for a table object error.
		if ($error = $table->getError()) {
			$this->setError($error);
			$false = false;
			return $false;
		}

		// Prime required properties.

		if (empty($table->id)) {
			$table->parent_id	= $this->getState('item.parent_id');
			$table->menutype	= $this->getState('item.menutype');
			$table->type		= $this->getState('item.type');
			$table->params		= '{}';
		}

		// If the link has been set in the state, possibly changing link type.
		if ($link = $this->getState('item.link')) {
			// Check if we are changing away from the actual link type.
			if (MenusHelper::getLinkKey($table->link) != MenusHelper::getLinkKey($link)) {
				$table->link = $link;
			}
		}

		switch ($table->type) {
			case 'alias':
				$table->component_id = 0;
				$args = array();

				parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
				break;

			case 'separator':
				$table->link = '';
				$table->component_id = 0;
				break;

			case 'url':
				$table->component_id = 0;

				parse_str(parse_url($table->link, PHP_URL_QUERY));
				break;

			case 'component':
			default:
				// Enforce a valid type.
				$table->type = 'component';

				// Ensure the integrity of the component_id field is maintained, particularly when changing the menu item type.
				$args = array();
				parse_str(parse_url($table->link, PHP_URL_QUERY), $args);

				if (isset($args['option'])) {
					// Load the language file for the component.
					$lang = &JFactory::getLanguage();
						$lang->load($args['option'], JPATH_ADMINISTRATOR, null, false, false)
					||	$lang->load($args['option'], JPATH_ADMINISTRATOR.'/components/'.$args['option'], null, false, false)
					||	$lang->load($args['option'], JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
					||	$lang->load($args['option'], JPATH_ADMINISTRATOR.'/components/'.$args['option'], $lang->getDefault(), false, false);

					// Determine the component id.
					$component = JComponentHelper::getComponent($args['option']);
					if (isset($component->id)) {
						$table->component_id = $component->id;
					}
				}

				// Set the parsed request arguments to the object.
				$table->request = $args;

				break;
		}

		// We have a valid type, inject it into the state for forms to use.
		$this->setState('item.type', $table->type);

		// Convert to the JObject before adding the params.
		$result = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		// Convert the params field to an array.
		$registry = new JRegistry;
		$registry->loadJSON($table->params);
		$result->params = $registry->toArray();

		// Merge the request arguments in to the params for a component.
		if ($table->type == 'component') {
			// Note that all request arguments become reserved parameter names.
			$args = array();
			parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
			$result->params = array_merge($result->params, $args);
		}

		if ($table->type == 'alias') {
			// Note that all request arguments become reserved parameter names.
			$args = array();
			parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
			$result->params = array_merge($result->params, $args);

		}

		if ($table->type == 'url') {
			// Note that all request arguments become reserved parameter names.
			$args = array();
			parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
			$result->params = array_merge($result->params, $args);
		}

		return $result;
	}

	/**
	 * Get the list of modules not in trash.
	 *
	 * @return	mixed	An array of module records (id, title, position), or false on error.
	 */
	public function getModules()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.position, a.published');
		$query->from('#__modules AS a');

		// Join on the module-to-menu mapping table.
		// We are only interested if the module is displayed on ALL or THIS menu item (or the inverse ID number).
		$query->select('map.menuid');
		$query->join('LEFT', '#__modules_menu AS map ON map.moduleid = a.id AND (map.menuid = 0 OR ABS(map.menuid) = '.(int) $this->getState('item.id').')');

		// Join on the asset groups table.
		$query->select('ag.title AS access_title');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		$query->where('a.published >= 0');
		$query->where('a.client_id = 0');
		$query->order('a.position, a.ordering');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($error = $db->getError()) {
			$this->setError($error);
			return false;
		}

		return $result;
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Menu', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * @param	object	A form object.
	 *
	 * @throws	Exception if there is an error in the form event.
	 * @since	1.6
	 */
	protected function preprocessForm($form)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Initialise variables.
		$link = $this->getState('item.link');
		$type = $this->getState('item.type');

		// Initialise form with component view params if available.
		if ($type == 'component') {

			$link = htmlspecialchars_decode($link);

			// Parse the link arguments.
			$args = array();
			parse_str(parse_url(htmlspecialchars_decode($link), PHP_URL_QUERY), $args);

			// Confirm that the option is defined.
			$option = '';
			if (isset($args['option'])) {
				// The option determines the base path to work with.
				$option = $args['option'];
				$base	= JPATH_SITE.'/components/'.$option;
			}

			// Confirm a view is defined.
			$formFile = false;
			if (isset($args['view'])) {
				$view = $args['view'];

				// Determine the layout to search for.
				if (isset($args['layout'])) {
					$layout = $args['layout'];
				} else {
					$layout = 'default';
				}

				$formFile = false;

				// Check for the layout XML file. Use standard xml file if it exists.
				$path = JPath::clean($base.'/views/'.$view.'/tmpl/'.$layout.'.xml');
				if (JFile::exists($path)) {
					$formFile = $path;
				}

				// if custom layout, get the xml file from the template folder
				// TODO: only look in the template folder for the menu item's template
				if (!$formFile) {
					$folders = JFolder::folders(JPATH_SITE.'/templates','',false,true);
					foreach($folders as $folder) {
						if (JFile::exists($folder.'/html/'.$option.'/'.$view.'/'.$layout.'.xml')) {
							$formFile = $folder.'/html/'.$option.'/'.$view.'/'.$layout.'.xml';
							break;
						}
					}
				}

				// TODO: Now check for a view manifest file
				// TODO: Now check for a component manifest file
			}

			if ($formFile) {
				// If an XML file was found in the component, load it first.
				// We need to qualify the full path to avoid collisions with component file names.

				//$form = parent::getForm($formFile, $formName, $formOptions, true);

				if ($form->loadFile($formFile, false, '/metadata') == false) {
					throw new Exception(JText::_('JModelForm_Error_loadFile_failed'));
				}
			}

			// Now load the component params.
			if ($isNew = false) {
				$path = JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$option.'/config.xml');
			} else {
				$path='null';
			}

			if (JFile::exists($path)) {
				// Add the component params last of all to the existing form.
				if (!$form->load($path, true, '/config')) {
					throw new Exception(JText::_('JModelForm_Error_loadFile_failed'));
				}
			}
		}

		// Load the specific type file
		if (!$form->loadFile('item_'.$type, false, false)) {
			throw new Exception(JText::_('JModelForm_Error_loadFile_failed'));
		}

		// Trigger the default form events.
		parent::preprocessForm($form);
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function rebuild()
	{
		// Initialiase variables.
		$db = $this->getDbo();
		$table = &$this->getTable();

		if (!$table->rebuild()) {
			$this->setError($table->getError());
			return false;
		}

		// Convert the parameters not in JSON format.
		$db->setQuery(
			'SELECT id, params' .
			' FROM #__menu' .
			' WHERE params NOT LIKE '.$db->quote('{%') .
			'  AND params <> '.$db->quote('')
		);

		$items = $db->loadObjectList();
		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		foreach ($items as &$item) {
			$registry = new JRegistry;
			$registry->loadJSON($item->params);
			$params = (string)$registry;

			$db->setQuery(
				'UPDATE #__menu' .
				' SET params = '.$db->quote($params).
				' WHERE id = '.(int) $item->id
			);
			if (!$db->query()) {
				$this->setError($error);
				return false;
			}
			unset($registry);
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
		// Initialise variables.
		$pk		= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('item.id');
		$isNew	= true;
		$db		= $this->getDbo();
		$table	= $this->getTable();

		// Load the row if saving an existing item.
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

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id)) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('item.id', $table->id);

		return true;
	}
	
	function _orderConditions($table = null)
	{
		$condition = array();
		return $condition;
	}
}
