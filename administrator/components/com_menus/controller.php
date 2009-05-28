<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	Menus
 */
class MenusController extends JController
{
	/**
	 * New menu item wizard
	 */

	function newItem()
	{
		JRequest::setVar('edit', false);
		$model	= &$this->getModel('Item');
		$view = &$this->getView('Item');
		$view->setModel($model, true);

		// Set the layout and display
		$view->setLayout('type');
		$view->type();
	}
	/**
	 * Edit menu item wizard
	 */

	function type()
	{
		JRequest::setVar('edit', true);
		$model	= &$this->getModel('Item');
		$view = &$this->getView('Item');
		$view->setModel($model, true);

		// Set the layout and display
		$view->setLayout('type');
		$view->type();
	}

	/**
	 * Edits a menu item
	 */
	function edit()
	{
		JRequest::setVar('edit', true);
		$model	= &$this->getModel('Item');
		$model->checkout();

		$view = &$this->getView('Item');
		$view->setModel($model, true);
		// Set the layout and display
		$view->setLayout('form');
		$view->edit();
	}

	/**
	 * Saves a menu item
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$model	= &$this->getModel('Item');
		$post	= JRequest::get('post');
		// allow name only to contain html
		$post['name'] = JRequest::getVar('name', '', 'post', 'string', JREQUEST_ALLOWHTML);
		$model->setState('request', $post);

		if ($model->store()) {
			$msg = JText::_('Menu item Saved');
		} else {
			$msg = JText::_('Error Saving Menu item');
		}

		$item = &$model->getItem();
		switch ($this->_task) {
			case 'apply':
				$this->setRedirect('index.php?option=com_menus&menutype='.$item->menutype.'&task=edit&cid[]='.$item->id.'' , $msg);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$item->menutype, $msg);
				break;
		}
	}

	/**
	* Cancels an edit operation
	*/
	function cancelItem()
	{
		global $mainframe;

		JRequest::checkToken() or jexit('Invalid Token');

		$menutype = $mainframe->getUserStateFromRequest('com_menus.menutype', 'menutype', 'mainmenu', 'string');

		$model = $this->getModel('item');
		$model->checkin();

		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menutype);
	}

	/**
	* Cancels an edit operation
	*/
	function cancel()
	{
		$this->setRedirect('index.php?option=com_menus');
	}

	/**
	* Form for copying item(s) to a specific menu
	*/
	function copy()
	{
		$model	= &$this->getModel('List');
		$view = &$this->getView('List');
		$view->setModel($model, true);
		$view->copyForm();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doCopy()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	 	= JRequest::getString('menu', '', 'post');
		$cid		= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		//Check to see of a menu was selected to copy the items too
		if (empty($menu))
		{
			$msg = JText::_('Please select a menu from the list');
			$mainframe->enqueueMessage($msg, 'message');
			return $this->execute('copy');
		}

		$model	= &$this->getModel('List');

		if ($model->copy($cid, $menu)) {
			$msg = JText::sprintf('Menu Items Copied to', count($cid), $menu);
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Form for moving item(s) to a specific menu
	*/
	function move()
	{
		$model	= &$this->getModel('List');
		$view = &$this->getView('List');
		$view->setModel($model, true);
		$view->moveForm();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doMove()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menu', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		//Check to see if a menu was selected to copy the items too
		if (empty($menu))
		{
			$msg = JText::_('Please select a menu from the list');
			$mainframe->enqueueMessage($msg, 'message');
			return $this->execute('move');
		}

		$model	= &$this->getModel('List');

		if ($model->move($cid, $menu)) {
			$msg = JText::sprintf('Menu Items Moved to', count($cid), $menu);
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = &$this->getModel('List');
		if ($model->setItemState($cid, 1)) {
			$msg = JText::sprintf('Menu Items Published', count($cid));
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function unpublish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = &$this->getModel('List');
		if ($model->setItemState($cid, 0)) {
			$msg = JText::sprintf('Menu Items Unpublished', count($cid));
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function orderup()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected'));
			return false;
		}

		$model = &$this->getModel('List');
		if ($model->orderItem($id, -1)) {
			$msg = JText::_('Menu Item Moved Up');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function orderdown()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected'));
			return false;
		}

		$model = &$this->getModel('List');
		if ($model->orderItem($id, 1)) {
			$msg = JText::_('Menu Item Moved Down');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function saveorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = &$this->getModel('List');
		if ($model->setOrder($cid, $menu)) {
			$msg = JText::_('New ordering saved');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accesspublic()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = &$this->getModel('List');
		if ($model->setAccess($cid, 0)) {
			$msg = JText::sprintf('Menu Items Set Public', count($cid));
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessregistered()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = &$this->getModel('List');
		if ($model->setAccess($cid, 1)) {
			$msg = JText::sprintf('Menu Items Set Registered', count($cid));
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessspecial()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = &$this->getModel('List');
		if ($model->setAccess($cid, 2)) {
			$msg = JText::sprintf('Menu Items Set Special', count($cid));
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function setdefault()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected'));
			return false;
		}

		$item = &JTable::getInstance('menu');
		$item->load($id);
		if (!$item->get('published')) {
			$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, JText::_('The Default Menu Item Must Be Published'));
			return false;
		}

		$model = &$this->getModel('List');
		if ($model->setHome($id)) {
			$msg = JText::_('Default Menu Item Set');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Get some variables from the request
		$menu	= JRequest::getVar('menutype', '', 'post', 'string');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (!count($cid)) {
			$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, JText::_('No Items Selected'));
			return false;
		}

		$model = &$this->getModel('List');
		if ($n = $model->toTrash($cid)) {
			$msg = JText::sprintf('Item(s) sent to the Trash', $n);
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus&task=view&menutype='.$menu, $msg);
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function view()
	{
		$model	= &$this->getModel('List');
		$view = &$this->getView('List');
		$view->setModel($model, true);
		$view->display();
	}


	/**
	 * Controller for view listing menu types and related statical info
	 * @param string The URL option
	 */
	function viewMenus()
	{

		$view = &$this->getView('Menus');
		$model	= &$this->getModel('Menutype');
		$view->setModel($model, true);
		$view->display();
	}


	/**
	 * Controller for view to edit a menu type
	 */
	function editMenu()
	{
		$view = &$this->getView('Menus');
		$model	= &$this->getModel('Menutype');
		$view->setModel($model, true);
		$view->editForm(true,null);

	}

	/**
	 * Controller for view to create a menu type
	 */
	function addMenu()
	{
		$view = &$this->getView('Menus');
		$model	= &$this->getModel('Menutype');
		$view->setModel($model, true);
		$view->editForm(false,null);
	}

	/**
	 * Controller for saving a menu type
	 */
	function saveMenu()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$db		= &JFactory::getDbo();
		$id		= JRequest::getVar('id', 0, 'post', 'int');

		$oldType = &JTable::getInstance('menutypes');
		$oldType->load($id);

		$menuType = &JTable::getInstance('menutypes');
		$menuType->bind(JRequest::get('post'));

		$isNew		= ($menuType->id == 0);
		$isChanged	= ($oldType->menutype != $menuType->menutype);

		if (!$menuType->check()) {
			JError::raiseWarning(500, $menuType->getError());
			$this->setRedirect('index.php?option=com_menus&task=editMenu', 'Please check your menu settings');
			return false;
		}

		if (!$menuType->store()) {
			JError::raiseWarning(500, $menuType->getError());
			$this->setRedirect('index.php?option=com_menus&task=editMenu', 'Please check your menu settings');
			return false;
		}

		if ($isNew)
		{
			if ($title = JRequest::getVar('module_title', $menuType->menutype, 'post', 'string'))
			{
				$module = &JTable::getInstance('module');
				$module->title 		= $title;
				$module->position 	= 'left';
				$module->module 	= 'mod_mainmenu';
				$module->published	= 0;
				$module->iscore 	= 0;
				$module->params		= 'menutype='. $menuType->menutype;

				// check then store data in db
				if (!$module->check()) {
					return JError::raiseWarning(500, $module->getError());
				}
				if (!$module->store()) {
					return JError::raiseWarning(500, $module->getError());
				}
				$module->checkin();
				$module->reorder('position='.$db->Quote($module->position));

				// module assigned to show on All pages by default
				// Clean up possible garbage first
				$query = 'DELETE FROM #__modules_menu WHERE moduleid = '.(int) $module->id;
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning(500, $db->getError());
				}

				// ToDO: Changed to become a Joomla! db-object
				$query = 'INSERT INTO #__modules_menu VALUES ('.(int) $module->id.', 0)';
				$db->setQuery($query);
				if (!$db->query()) {
					return JError::raiseWarning(500, $db->getError());
				}
			}

			$msg = JText::sprintf('New Menu created', $menuType->menutype);
		}
		else if ($isChanged)
		{
			$oldTerm = $oldType->menutype;
			$newTerm = $menuType->menutype;

			// change menutype being of all mod_mainmenu modules calling old menutype
			$query = 'SELECT id'
			. ' FROM #__modules'
			. ' WHERE module = "mod_mainmenu"'
			. ' AND params LIKE "%menutype='.$db->getEscaped($oldTerm).'%"'
			;
			$db->setQuery($query);
			$modules = $db->loadResultArray();

			foreach ($modules as $id)
			{
				$row = &JTable::getInstance('module');
				$row->load($id);

				$row->params = str_replace($oldTerm, $newTerm, $row->params);

				// check then store data in db
				if (!$row->check()) {
					return JError::raiseWarning(500, $db->getError());
				}
				if (!$row->store()) {
					return JError::raiseWarning(500, $db->getError());
				}
				$row->checkin();
			}

			// change menutype of all menuitems using old menutype
			$query = 'UPDATE #__menu'
			. ' SET menutype = '.$db->Quote($newTerm)
			. ' WHERE menutype = '.$db->Quote($oldTerm)
			;
			$db->setQuery($query);
			$db->query();

			$msg = JText::_('Menu Items & Modules updated');
		}

		$this->setRedirect('index.php?option=com_menus', $msg);
	}

	/**
	 * Controller for a view to confirm the deletion of a menu type
	 */
	function deleteMenu()
	{
		$view = &$this->getView('Menus');
		$model	= &$this->getModel('Menutype');
		$view->setModel($model, true);
		$view->deleteForm();
	}

	/**
	 * Delete a menu
	 */
	function doDeleteMenu()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$id = JRequest::getVar('id', 0, '', 'int');
		if ($id <= 0) {
			JError::raiseWarning(500, JText::_('Invalid ID provided'));
			$this->setRedirect('index.php?option=com_menus');
			return false;
		}

		$model = &$this->getModel('Menutype');
		if (!$model->canDelete()) {
			JError::raiseWarning(500, $model->getError());
			$this->setRedirect('index.php?option=com_menus');
			return false;
		}
		$err = null;
		if (!$model->delete()) {
			 $err = $model->getError();
		}
		$this->setRedirect('index.php?option=com_menus', $err);
	}

	/**
	* Compiles a list of the articles you have selected to Copy
	*/
	function copyMenu()
	{
		$view = &$this->getView('Menus');
		$model	= &$this->getModel('Menutype');
		$view->setModel($model, true);
		$view->copyForm();
	}

	/**
	* Copies a complete menu, all its items and creates a new module, using the name speified
	*/
	function doCopyMenu()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$db				= &JFactory::getDbo();
		$type			= JRequest::getVar('type', '', 'post', 'string');
		$menu_name		= JRequest::getVar('menu_name', 'New Menu', 'post', 'string');
		$module_name	= JRequest::getVar('module_name', 'New Module', 'post', 'string');

		// check for unique menutype for new menu copy
		$query = 'SELECT params' .
				' FROM #__modules' .
				' WHERE module = "mod_mainmenu"';
		$db->setQuery($query);
		$menus = $db->loadResultArray();

		foreach ($menus as $menu)
		{
			$params = new JParameter($menu);
			if ($params->get('menutype') == $menu_name) {
				JError::raiseWarning(500, JText::_('ERRORMENUNAMEEXISTS'));
				$this->setRedirect('index.php?option=com_menus');
				return;
			}
		}

		// copy the menu items
		$mids 		= JRequest::getVar('mids', array(), 'post', 'array');
		JArrayHelper::toInteger($mids);
		$total 		= count($mids);
		$copy 		= &JTable::getInstance('menu');
		$original 	= &JTable::getInstance('menu');
		sort($mids);
		$a_ids 		= array();

		foreach($mids as $mid)
		{
			$original->load($mid);
			$copy 			= $original;
			$copy->id 		= NULL;
			$copy->parent 	= $a_ids[$original->parent];
			$copy->menutype = $menu_name;
			$copy->home 	= 0;

			if (!$copy->check()) {
				JError::raiseWarning(500, $copy->getError());
				$this->setRedirect('index.php?option=com_menus');
				return;
			}
			if (!$copy->store()) {
				JError::raiseWarning(500, $copy->getError());
				$this->setRedirect('index.php?option=com_menus');
				return;
			}
			$a_ids[$original->id] = $copy->id;
		}

		// create the module copy
		$row = &JTable::getInstance('module');
		$row->load(0);
		$row->title 	= $module_name;
		$row->iscore 	= 0;
		$row->published = 1;
		$row->position 	= 'left';
		$row->module 	= 'mod_mainmenu';
		$row->params 	= 'menutype='. $menu_name;

		if (!$row->check()) {
			JError::raiseWarning(500, $db->getError());
			$this->setRedirect('index.php?option=com_menus');
			return;
		}
		if (!$row->store()) {
			JError::raiseWarning(500, $db->getError());
			$this->setRedirect('index.php?option=com_menus');
			return;
		}
		$row->checkin();
		$row->reorder('position='.$db->Quote($row->position));
		// module assigned to show on All pages by default
		// ToDO: Changed to become a Joomla! db-object
		$query = 'INSERT INTO #__modules_menu' .
				' VALUES ('.(int) $row->id.', 0)';
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg(true));
			$this->setRedirect('index.php?option=com_menus');
			return;
			//echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
			//$mainframe->close();
		}

		// Insert the menu type
		$query = 'INSERT INTO `#__menu_types`  (`menutype` , `title` , `description`) ' .
				' VALUES ('.$db->Quote($menu_name).', '.$db->Quote($menu_name).', "")';
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg(true));
			$this->setRedirect('index.php?option=com_menus');
			return;
			//echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
			//$mainframe->close();
		}

		$msg = JText::sprintf('Copy of Menu created', $type, $total);
		$mainframe->redirect('index.php?option=com_menus', $msg);
	}
}
