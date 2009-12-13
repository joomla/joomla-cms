<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 */
class ContactControllerContact extends JController
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->registerTask('save2copy',	'save');
		$this->registerTask('save2new',		'save');
		$this->registerTask('apply',		'save');
		$this->registerTask('editList',		'edit');
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('archive',		'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('report',		'publish');
	}

	/**
	 * Display the view
	 */
	function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_contact', false));
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel()
	{
		return parent::getModel('Contact', '', array('ignore_request' => true));
	}

	/**
	 * Method to add a new contact.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialise variables.
		$app = &JFactory::getApplication();

		// Clear the menu item edit information from the session.
		$app->setUserState('com_contact.edit.contact.id',	null);
		$app->setUserState('com_contact.edit.contact.data',	null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&layout=edit', false));
	}

	/**
	 * Method to edit an existing contact
	 *
	 * Sets object ID in the session from the request, checks the item out, and then redirects to the edit page.
	 */
	function edit()
	{
		// Initialise variables.
		$app	= &JFactory::getApplication();
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id =  (empty($ids) ? JRequest::getInt('contact_id') : (int) array_pop($ids));

		// Get the previous row id (if any) and the current row id.
		$previousId	= (int) $app->getUserState('com_contact.edit.contact.id');
		$app->setUserState('com_contact.edit.contact.id', $id);

		// Get the menu item model.
		$model = &$this->getModel();

		// Check that this is not a new item.
		if ($id > 0)
		{
			$item = $model->getItem($id);

			// If not already checked out, do so.
			if ($item->checked_out == 0)
			{
				if (!$model->checkout($id))
				{
					// Check-out failed, go back to the list and display a notice.
					$message = JText::sprintf('JError_Checkout_failed', $model->getError());
					$this->setRedirect('index.php?option=com_contact&view=contact&contact_id='.$id, $message, 'error');
					return false;
				}
			}

		}
		// Check-out succeeded, push the new row id into the session.
		$app->setUserState('com_contact.edit.contact.id',	$id);
		$app->setUserState('com_contact.edit.contact.data',	null);
		$this->setRedirect('index.php?option=com_contact&view=contact&layout=edit');

		return true;
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 */
	function cancel()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));
		// Initialise variables.
		$app = &JFactory::getApplication();

		// Get the previous  id (if any) and the current  id.
		$previousId	= (int) $app->getUserState('com_contact.edit.contact.id');

		// Get the  item model.
		$model = &$this->getModel();

		// If rows ids do not match, checkin previous row.
		if (!$model->checkin($previousId))
		{
			// Attempt to check-in the current contact
			// Check-in failed, go back to the  item and display a notice.
			$message = JText::sprintf('JError_Checkin_failed', $model->getError());
			return false;
		}

		// Clear the  item edit information from the session.

		$app->setUserState('com_contact.edit.contact.id',	null);
		$app->setUserState('com_contact.edit.contact.data',	null);


		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contacts', false));
	}

	/**
	 * Method to save a contact
	 *
	 * @since	1.0
	 */
	function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Item');
		$task	= $this->getTask();

		// Get posted form variables.
		$data		= JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_contact.edit.contact.id');

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if (!$model->checkin())
			{
				// Check-in failed, go back to the item and display a notice.
				$message = JText::sprintf('JError_Checkin_saved', $model->getError());
				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$data['id']	= 0;
			$task		= 'apply';
		}

		// Validate the posted data.
		$form	= &$model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				}
				else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_contact.edit.contact.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&layout=edit', false));
			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Save the data in the session.
			$app->setUserState('com_contact.edit.contact.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JError_Save_failed', $model->getError()), 'notice');
			return false;
		}

		// Save succeeded, check-in the row.
		if (!$model->checkin())
		{
			// Check-in failed, go back to the row and display a notice.
			$message = JText::sprintf('JError_Checkin_saved', $model->getError());
			return false;
		}

		$this->setMessage(JText::_('JController_Save_success'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the row data in the session.


				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&layout=edit', false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$app->setUserState('com_contact.edit.contact.id',	null);
				$app->setUserState('com_contact.edit.contact.data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&layout=edit', false));
				break;

			default:
				// Clear the row id and data in the session.
				$app->setUserState('com_contact.edit.contact.id',	null);
				$app->setUserState('com_contact.edit.contact.data',	null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contacts', false));
				break;
		}
	}

	/**
	 * Removes an item
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to remove from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if (!$model->delete($cid)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_contact&view=contacts');
	}

	/**
	 * Method to publish a list of taxa
	 *
	 * @since	1.0
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		// Get items to publish from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('publish' => 1, 'unpublish' => 0, 'archive'=>-1, 'trash' => -2, 'report'=>-3);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else {
			// Get the model.
			$model	= $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish($cid, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_contact&view=contacts');
	}

	/**
	 * Changes the order of an item
	 */
	function ordering()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		$cid	= JRequest::getVar('cid', null, 'post', 'array');
		$inc	= $this->getTask() == 'orderup' ? -1 : +1;

		$model = & $this->getModel();
		$model->ordering($cid, $inc);

		$this->setRedirect('index.php?option=com_contact&view=contacts');
	}
}