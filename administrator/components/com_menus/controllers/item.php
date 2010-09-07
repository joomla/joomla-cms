<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controllerform' );

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusControllerItem extends JControllerForm
{
	/**
	 * Method to add a new menu item.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function add()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Clear the row edit information from the session.
		$app->setUserState('com_menus.edit.item.id',	null);
		$app->setUserState('com_menus.edit.item.data',	null);
		$app->setUserState('com_menus.edit.item.type',	null);
		$app->setUserState('com_menus.edit.item.link',	null);

		// Check if we are adding for a particular menutype
		$menuType = $app->getUserStateFromRequest($this->context.'.filter.menutype', 'menutype', 'mainmenu');

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit&menutype='.$menuType, false));
	}

	/**
	 * Method to run batch opterations.
	 *
	 * @return	void
	 * @since	1.6
	 */
	function batch()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Item');
		$vars	= JRequest::getVar('batch', array(), 'post', 'array');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_menus&view=items');

		// Attempt to run the batch operation.
		if ($model->batch($vars, $cid)) {
			$this->setMessage(JText::_('COM_MENUS_BATCH_SUCCESS'));
			return true;
		} else {
			$this->setMessage(JText::_(JText::sprintf('COM_MENUS_ERROR_BATCH_FAILED', $model->getError())));
			return false;
		}
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.

		$app	= JFactory::getApplication();
		// Get the previous menu item id (if any) and the current menu item id.
		$previousId	= (int) $app->getUserState('com_menus.edit.item.id');

		$model	= $this->getModel('Item');

		// If rows ids do not match, checkin previous row.
		if (!$model->checkin($previousId)) {
		// Check-in failed, go back to the menu item and display a notice.
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_content&view=item&layout=edit', $message, 'error');
			return false;

		}

		// Clear the row edit information from the session.
		$app->setUserState('com_menus.edit.item.id',	null);
		$app->setUserState('com_menus.edit.item.data',	null);
		$app->setUserState('com_menus.edit.item.type',	null);
		$app->setUserState('com_menus.edit.item.link',	null);

	// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items', false));
	}

	/**
	 * Method to edit an existing menu item.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function edit()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		$pks	= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id		=  (empty($pks) ? JRequest::getInt('item_id') : (int) array_pop($pks));

		// Get the menu item model.
		$model	= $this->getModel('Item');

		// Check that this is not a new item.
		if ($id > 0) {
			$item = $model->getItem($id);

			// If not already checked out, do so.
			if ($item->checked_out == 0) {
				if (!$model->checkout($id)) {
					// Check-out failed, go back to the list and display a notice.
					$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()), 'warning');
					return false;
				}
			}
		}

		// Push the new row id into the session.
		$app->setUserState('com_menus.edit.item.id',	$id);
		$app->setUserState('com_menus.edit.item.data',	null);
		$app->setUserState('com_menus.edit.item.type',	null);
		$app->setUserState('com_menus.edit.item.link',	null);

		$this->setRedirect('index.php?option=com_menus&view=item&layout=edit');

		return true;
	}

	/**
	 * Method to save a menu item.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Item');
		$task	= $this->getTask();

		// Get the posted values from the request.
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_menus.edit.item.id');

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy') {
			// Check-in the original row.
			if (!$model->checkin()) {
				// Check-in failed, go back to the item and display a notice.
				$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'warning');
				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$data['id']	= 0;
			$task		= 'apply';
		}

		// Validate the posted data.
		// This post is made up of two forms, one for the item and one for params.
		$form = $model->getForm($data);
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data = $model->validate($form, $data);

		// Check for the special 'request' entry.
		if ($data['type'] == 'component' && isset($data['request']) && is_array($data['request']) && !empty($data['request'])) {
			// Parse the submitted link arguments.
			$args = array();
			parse_str(parse_url($data['link'], PHP_URL_QUERY), $args);

			// Merge in the user supplied request arguments.
			$args = array_merge($args, $data['request']);
			$data['link'] = 'index.php?'.urldecode(http_build_query($args,'','&'));
			unset($data['request']);
		}

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_menus.edit.item.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data)) {
			// Save the data in the session.
			$app->setUserState('com_menus.edit.item.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
			return false;
		}

		// Save succeeded, check-in the row.
		if (!$model->checkin()) {
			// Check-in failed, go back to the row and display a notice.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
			return false;
		}

		$this->setMessage(JText::_('COM_MENUS_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task) {
			case 'apply':
				// Set the row data in the session.
				$app->setUserState('com_menus.edit.item.id',	$model->getState('item.id'));
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$app->setUserState('com_menus.edit.item.id',	null);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
				break;

			default:
				// Clear the row id and data in the session.
				$app->setUserState('com_menus.edit.item.id',	null);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items', false));
				break;
		}
	}

	/**
	 * Sets the type of the menu item currently being editted.
	 *
	 * @return	void
	 * @since	1.6
	 */
	function setType()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the posted values from the request.
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Get the type.
		$type = $data['type'];

		$type = json_decode(base64_decode($type));
		$title = isset($type->title) ? $type->title : null;
		if ($title != 'alias' && $title != 'separator' && $title != 'url') {
			$title = 'component';
		}

		$app->setUserState('com_menus.edit.item.type',	$title);
		if ($title == 'component') {
			if (isset($type->request)) {
				$component = JComponentHelper::getComponent($type->request->option);
				$data['component_id'] = $component->id;
				if (isset($type->request->layout)) {
					$app->setUserState(
						'com_menus.edit.item.link',
						'index.php?option='.$type->request->option.'&view='.$type->request->view.'&layout='.$type->request->layout
					);
				} else {
					$app->setUserState(
						'com_menus.edit.item.link',
						'index.php?option='.$type->request->option.'&view='.$type->request->view);
				}
			}
		}
		// If the type is alias you just need the item id from the menu item referenced.
		else if ($title == 'alias') {
			$app->setUserState('com_menus.edit.item.link', 'index.php?Itemid=');
		}

		unset($data['request']);
		$data['type'] = $title;
		$data['link'] = $app->getUserState('com_menus.edit.item.link');

		//Save the data in the session.
		$app->setUserState('com_menus.edit.item.data', $data);

		$this->type = $type;
		$this->setRedirect('index.php?option=com_menus&view=item&layout=edit');
	}
}