<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Include dependancies.
jimport('joomla.application.component.modelform');
jimport('joomla.database.query');
require_once JPATH_COMPONENT.DS.'helpers'.DS.'menus.php';

/**
 * Menu Item Model for Menus.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusModelItem extends JModelForm
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context		= 'com_menus.item';

	/**
	 * Returns a reference to the a Table object, always creating it
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function &getTable($type = 'Menu', $prefix = 'JTable', $config = array())
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
		$app	= &JFactory::getApplication('administrator');

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

		if ($type = $app->getUserState('com_menus.edit.item.type')){
	//		$type = JRequest::getCmd('type', 'url');
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
	 * Method to get a menu item.
	 *
	 * @param	integer	An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialize variables.
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('item.id');

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
			$table->parent_id	= $this->getState('item.parent_id');
			$table->menutype	= $this->getState('item.menutype');
			$table->type		= $this->getState('item.type');
			$table->params		= '{}';
		}

		// If the link has been set in the state, possibly changing link type.
		if ($link = $this->getState('item.link'))
		{
			// Check if we are changing away from the actual link type.
			if (MenusHelper::getLinkKey($table->link) != MenusHelper::getLinkKey($link)) {
				$table->link = $link;
			}
		}

		switch ($table->type)
		{
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
				if (isset($args['option']))
				{
					// Load the language file for the component.
					$lang = &JFactory::getLanguage();
					$lang->load($args['option']);

					// Determine the component id.
					$component = JComponentHelper::getComponent($args['option']);
					if (isset($component->id)) {
						$table->component_id = $component->id;
					}
				}


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
		if ($table->type == 'component')
		{
			// Note that all request arguments become reserved parameter names.
			$args = array();
			parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
			$result->params = array_merge($result->params, $args);
		}
		if ($table->type == 'alias')
		{
			// Note that all request arguments become reserved parameter names.
			$args = array();
			parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
			$result->params = array_merge($result->params, $args);

		}
		if ($table->type == 'url')
		{
			// Note that all request arguments become reserved parameter names.
			$args = array();
			parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
			$result->params = array_merge($result->params, $args);
		}

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
		// Initialize variables.
		$app	= &JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('item', 'com_menus.item', array('array' => 'jform', 'event' => 'onPrepareForm'), true);

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
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
	 * Method to get a form object for the menu item params.
	 *
	 * This is one of the most complicated aspects of the menu item.
	 * If it's an alias, a separator or URL, we just need a simple form for the extra params.
	 * If it's a component, we need to look first for layout, view and component form data.
	 * Then we need to add the component configuration if it's available. Note that hidden fieldset groups
	 * with not display in the menu edit form.
	 *
	 * @param	string		An optional type (component, url, alias or separator).
	 * @param	string		An optional link
	 *
	 * @return	mixed		A JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getParamsForm($type = null, $link = null)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Initialise variables.
		$form			= null;
		$formFile		= null;
		$formName		= 'com_menus.item.params';
		$formOptions	= array('array' => 'jformparams', 'event' => 'onPrepareForm');

		// Determine the link type.
		if (empty($type)) {
			$type = $this->getState('item.type');
		}

		if (empty($link))
		{
			// If the link not supplied, try to load it.
			if ($item = &$this->getItem()) {
				$link = htmlspecialchars_decode($item->link);
			}
		}

		// Initialise form with component view params if available.

		if ($type == 'component')
		{
			// Parse the link arguments.
			$args = array();
			parse_str(parse_url(htmlspecialchars_decode($link), PHP_URL_QUERY), $args);

			// Confirm that the option is defined.
			if (isset($args['option']))
			{
				// The option determines the base path to work with.
				$option = $args['option'];
				$base	= JPATH_SITE.DS.'components'.DS.$option;
			}
				// Confirm a view is defined.
				if (isset($args['view']))
				{
					$view = $args['view'];

					// Determine the layout to search for.
					if (isset($args['layout'])) {
						$layout = $args['layout'];
					}
					else {
						$layout = 'default';
					}

					$formFile = false;
					$folders = JFolder::folders(JPATH_SITE.DS.'templates','',false,true);
					foreach($folders as $folder)
					{
						if (JFile::exists($folder.DS.'html'.DS.$option.DS.$view.DS.$layout.'.xml')) {
							$formFile = $folder.DS.'html'.DS.$option.DS.$view.DS.$layout.'.xml';
							break;
						}
					}

					if(!$formFile)
					{
					// Check for the layout XML file.
						$path = JPath::clean($base.DS.'views'.DS.$view.DS.'tmpl'.DS.$layout.'.xml');
						if (JFile::exists($path)) {
							$formFile = $path;
						}
				//	}
					// TODO: Now check for a view manifest file
					// TODO: Now check for a component manifest file
					}
				}

			if ($formFile)
			{
				// If an XML file was found in the component, load it first.
				// We need to qualify the full path to avoid collisions with component file names.
				$form = parent::getForm($formFile, $formName, $formOptions, true);

				// Check for an error.
				if (JError::isError($form)) {
					$this->setError($form->getMessage());
					return false;
				}
			}

			// Now load the component params.
			if ($isNew=false){
			$path = JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS. $option.DS.'config.xml');}
			else $path='null';
			if (JFile::exists($path))
			{
				if (empty($form))
				{
					// It's possible the form hasn't been defined yet.
					$form = parent::getForm($path, $formName, $formOptions, true);

					// Check for an error.
					if (JError::isError($form)) {
						$this->setError($form->getMessage());
						return false;
					}
				}
				else
				{
					// Add the component params last of all to the existing form.
					$form->load($path, true, false);
				}
			}
		}


		// If no component file found, or not a component, create the form.
		if (empty($form))
		{
			$form = parent::getForm('item_'.$type, $formName, $formOptions, true);

			// Check for an error.
			if (JError::isError($form)) {
				$this->setError($form->getMessage());
				return false;
			}
		}
		else {
			$form->load('item_'.$type, true, false);
		}

		return $form;
	}


	/**
	 * Get the list of modules not in trash.
	 *
	 * @return	mixed	An array of module records (id, title, position), or false on error.
	 */
	public function getModules()
	{
		$query = new JQuery;

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

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		if ($error = $this->_db->getError())
		{
			$this->setError($error);
			return false;
		}

		return $result;
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	$pk The numeric id of a row
	 * @return	boolean	False on failure or error, true otherwise.
	 */
	public function checkin($pk = null)
	{
		// Initialize variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('item.id');

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
		// Initialize variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');

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
		$pk		= (!empty($data['id'])) ? $data['id'] : (int)$this->getState('item.id');
		$isNew	= true;

		// Get a row instance.
		$table = &$this->getTable();

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
			$this->setError($table->getError());
			return false;
		}

		// Rebuild the tree path.
		if (!$table->rebuildPath($table->id)) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('item.id', $table->id);

		// So this is where things get a little bit insane ...

		// If menuid is 0, there is only one entry in the mapping table.
		// If menuid is nothing, there could be other entries in the mapping table, but not be zero.
		// If menuid is positive or negative, there could also be other entries in the table.

		$map = JArrayHelper::getValue($data, 'map', array(), 'array');

		$drops	= array();
		$adds	= array();

		foreach ($map as $moduleId => $menuId)
		{
			$moduleId	= (int) $moduleId;

			// Check that we have a module id.
			if (empty($moduleId)) {
				continue;
			}

			// Check if the menuid is set to ALL
			if (is_numeric($menuId) && (int) $menuId == 0)
			{
				// Drop all other maps for this module.
				$drops[]	= '(moduleid = '.$moduleId.')';

				// Add the map for this module to show on all pages.
				$adds[]		= '('.$moduleId.', 0)';
			}
			else
			{
				// Drop all other maps for this module to ALL pages.
				$drops[] = '(moduleid = '.$moduleId.' AND menuid = 0)';

				if ($menuId == 1 || $menuId == -1)
				{
					// Add the map for this module to show/hide on this page.
					$adds[] = '('.$moduleId.', '.(int) $table->id * $menuId.')';
				}
			}
		}

		// Preform the drops.
		if (!empty($drops))
		{
			$this->_db->setQuery(
				'DELETE FROM #__modules_menu' .
				' WHERE '.implode(' OR ', $drops)
			);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			echo $this->_db->getQuery();
		}

		// Perform the inserts.
		if (!empty($adds))
		{
			$this->_db->setQuery(
				'INSERT INTO #__modules_menu (moduleid, menuid)' .
				' VALUES '.implode(',', $adds)
			);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			echo $this->_db->getQuery();
		}

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
			if (!$table->delete((int) $pk))
			{
				$this->setError($table->getError());
				return false;
			}
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
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('item.id');

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

		// Convert the parameters not in JSON format.
		$this->_db->setQuery(
			'SELECT id, params' .
			' FROM #__menu' .
			' WHERE params NOT LIKE '.$this->_db->quote('{%') .
			'  AND params <> '.$this->_db->quote('')
		);

		$items = $this->_db->loadObjectList();
		if ($error = $this->_db->getErrorMsg())
		{
			$this->setError($error);
			return false;
		}

		foreach ($items as &$item)
		{
			$registry = new JRegistry;
			$registry->loadJSON($item->params);
			$params = $registry->toString();

			$this->_db->setQuery(
				'UPDATE #__menu' .
				' SET params = '.$this->_db->quote($params).
				' WHERE id = '.(int) $item->id
			);
			if (!$this->_db->query())
			{
				$this->setError($error);
				return false;
			}
			unset($registry);
		}

		return true;
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

	/**
	 * Batch move menu items to a new menu or parent.
	 *
	 * @param	int		The new menu or sub-item.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function _batchMove($value, $pks)
	{
		// $value comes as {menutype}.{parent_id}
		$parts		= explode('.', $value);
		$menuType	= $parts[0];
		$parentId	= (int) JArrayHelper::getValue($parts, 1, 0);

		$table	= &$this->getTable();
		$db		= &$this->getDbo();

		// Check that the parent exists.
		if ($parentId)
		{
			if (!$table->load($parentId))
			{
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Non-fatal error
					$this->setError(JText::_('Menus_Batch_Move_parent_not_found'));
					$parentId = 0;
				}
			}
		}

		// We are going to store all the children and just moved the menutype
		$children = array();

		// Parent exists so we let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$table->load($pk))
			{
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Not fatal error
					$this->setError(JText::sprintf('Menus_Batch_Move_row_not_found', $pk));
					continue;
				}
			}

			// Set the new location in the tree for the node.
			$table->setLocation($parentId, 'last-child');

			// Check if we are moving to a different menu
			if ($menuType != $table->menutype)
			{
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
		if (!empty($children))
		{
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
	 * Batch copy menu items to a new menu or parent.
	 *
	 * @param	int		The new menu or sub-item.
	 * @param	array	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 */
	protected function _batchCopy($value, $pks)
	{
		// $value comes as {menutype}.{parent_id}
		$parts		= explode('.', $value);
		$menuType	= $parts[0];
		$parentId	= (int) JArrayHelper::getValue($parts, 1, 0);

		$table	= &$this->getTable();
		$db		= &$this->getDbo();

		// Check that the parent exists
		if ($parentId)
		{
			if (!$table->load($parentId))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Non-fatal error
					$this->setError(JText::_('Menus_Batch_Move_parent_not_found'));
					$parentId = 0;
				}
			}
		}

		// If the parent is 0, set it to the ID of the root item in the tree
		if (empty($parentId))
		{
			if (!$parentId = $table->getRootId()) {
				$this->setError($this->_db->getErrorMsg());
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

		if ($error = $db->getErrorMsg())
		{
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
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('Menus_Batch_Move_row_not_found', $pk));
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
		if (!$table->rebuildTree()) {
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
}
