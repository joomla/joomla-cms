<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
		$app		= JFactory::getApplication();
		$context	= 'com_menus.edit.item';

		$result = parent::add();
		if ($result) {
			$app->setUserState($context.'.type',	null);
			$app->setUserState($context.'.link',	null);

			$menuType = $app->getUserStateFromRequest($this->context.'.filter.menutype', 'menutype', 'mainmenu', 'cmd');

			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&menutype='.$menuType.$this->getRedirectToItemAppend(), false));
		}

		return $result;
	}

	/**
	 * Method to run batch opterations.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function batch($model)
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$model	= $this->getModel('Item', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items'.$this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function cancel($key = null)
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$context	= 'com_menus.edit.item';
		$result		= parent::cancel();

		if ($result) {
			// Clear the ancillary data from the session.
			$app->setUserState($context.'.type',	null);
			$app->setUserState($context.'.link',	null);
		}
	}

	/**
	 * Method to edit an existing menu item.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		$result	= parent::edit();

		if ($result) {
			// Push the new ancillary data into the session.
			$app->setUserState('com_menus.edit.item.type',	null);
			$app->setUserState('com_menus.edit.item.link',	null);
		}

		return true;
	}

	/**
	 * Method to save a menu item.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$model		= $this->getModel('Item', '', array());
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$task		= $this->getTask();
		$context	= 'com_menus.edit.item';
		$recordId	= JRequest::getInt('id');

		if (!$this->checkEditId($context, $recordId)) {
			// Somehow the person just went to the form and saved it - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items'.$this->getRedirectToListAppend(), false));

			return false;
		}

		// Populate the row id from the session.
		$data['id'] = $recordId;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy') {
			// Check-in the original row.
			if ($model->checkin($data['id']) === false) {
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
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_menus.edit.item.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data)) {
			// Save the data in the session.
			$app->setUserState('com_menus.edit.item.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));

			return false;
		}

		// Save succeeded, check-in the row.
		if ($model->checkin($data['id']) === false) {
			// Check-in failed, go back to the row and display a notice.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));

			return false;
		}

		$this->setMessage(JText::_('COM_MENUS_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task) {
			case 'apply':
				// Set the row data in the session.
				$recordId = $model->getState($this->context.'.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);
				$app->setUserState('com_menus.edit.item.menutype',	$model->getState('item.menutype'));

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend(), false));
				break;

			default:
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
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
		$app		= JFactory::getApplication();

		// Get the posted values from the request.
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$recordId	= JRequest::getInt('id');

		// Get the type.
		$type		= $data['type'];

		$type		= json_decode(base64_decode($type));
		$title		= isset($type->title) ? $type->title : null;
		$recordId	= isset($type->id) ? $type->id : 0;

		if ($title != 'alias' && $title != 'separator' && $title != 'url') {
			$title = 'component';
		}

		$app->setUserState('com_menus.edit.item.type',	$title);
		if ($title == 'component') {
			if (isset($type->request)) {
				$component = JComponentHelper::getComponent($type->request->option);
				$data['component_id'] = $component->id;

				$app->setUserState(
					'com_menus.edit.item.link',
					'index.php?' . JURI::buildQuery((array)$type->request));
			}
		}
		// If the type is alias you just need the item id from the menu item referenced.
		else if ($title == 'alias') {
			$app->setUserState('com_menus.edit.item.link', 'index.php?Itemid=');
		}

		unset($data['request']);
		$data['type'] = $title;
		if (JRequest::getCmd('fieldtype') == 'type') {
			$data['link'] = $app->getUserState('com_menus.edit.item.link');
		}

		//Save the data in the session.
		$app->setUserState('com_menus.edit.item.data', $data);

		$this->type = $type;
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
	}
}
