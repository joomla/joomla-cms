<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblink controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @version		1.6
 */
class WeblinksControllerWeblink extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('apply',	'save');
		$this->registerTask('save2new',	'save');
	}

	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks', false));
	}

	/**
	 * Method to add a new weblink.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialize variables.
		$app = &JFactory::getApplication();

		// Clear the level edit information from the session.
		$app->setUserState('com_weblinks.edit.weblink.id', null);
		$app->setUserState('com_weblinks.edit.weblink.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
	}

	/**
	 * Method to edit an existing weblink.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Weblink', 'WeblinksModel');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Get the previous weblink id (if any) and the current weblink id.
		$previousId		= (int) $app->getUserState('com_weblinks.edit.weblink.id');
		$weblinkId		= (int) (count($cid) ? $cid[0] : JRequest::getInt('weblink_id'));

		// If weblink ids do not match, checkin previous weblink.
		if (($previousId > 0) && ($weblinkId != $previousId)) {
			if (!$model->checkin($previousId)) {
				// Check-in failed, go back to the weblink and display a notice.
				$message = JText::sprintf('JError_Checkin_failed', $model->getError());
				$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit', $message, 'error');
				return false;
			}
		}

		// Attempt to check-out the new weblink for editing and redirect.
		if (!$model->checkout($weblinkId)) {
			// Check-out failed, go back to the list and display a notice.
			$message = JText::sprintf('JError_Checkout_failed', $model->getError());
			$this->setRedirect('index.php?option=com_weblinks&view=weblink&weblink_id='.$weblinkId, $message, 'error');
			return false;
		}
		else {
			// Check-out succeeded, push the new weblink id into the session.
			$app->setUserState('com_weblinks.edit.weblink.id',	$weblinkId);
			$app->setUserState('com_weblinks.edit.weblink.data', null);
			$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit');
			return true;
		}
	}

	/**
	 * Method to cancel an edit
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	public function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Weblink', 'WeblinksModel');

		// Get the weblink id.
		$weblinkId = (int) $app->getUserState('com_weblinks.edit.weblink.id');

		// Attempt to check-in the current weblink.
		if ($weblinkId) {
			if (!$model->checkin($weblinkId)) {
				// Check-in failed, go back to the weblink and display a notice.
				$message = JText::sprintf('JError_Checkin_failed', $model->getError());
				$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit&hidemainmenu=1', $message, 'error');
				return false;
			}
		}

		// Clean the session data and redirect.
		$app->setUserState('com_weblinks.edit.weblink.id',		null);
		$app->setUserState('com_weblinks.edit.weblink.data',	null);
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks', false));
	}

	/**
	 * Method to save a weblink.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= $this->getModel('Weblink');
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

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
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_weblinks.edit.weblink.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Attempt to save the weblink.
		$return = $model->save($data);

		if ($return === false) {
			// Save failed, go back to the weblink and display a notice.
			$message = JText::sprintf('JError_Save_failed', $model->getError());
			$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		// Save succeeded, check-in the weblink.
		if (!$model->checkin()) {
			// Check-in failed, go back to the weblink and display a notice.
			$message = JText::sprintf('JError_Checkin_saved', $model->getError());
			$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		$this->setMessage(JText::_('JController_Save_success'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task)
		{
			case 'apply':
				// Set the row data in the session.
				$app->setUserState('com_weblinks.edit.weblink.id',		$model->getState('weblink.id'));
				$app->setUserState('com_weblinks.edit.weblink.data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
				break;

			case 'save2new':
				// Clear the member id and data from the session.
				$app->setUserState('com_weblinks.edit.weblink.id', null);
				$app->setUserState('com_weblinks.edit.weblink.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
				break;

			default:
				// Clear the member id and data from the session.
				$app->setUserState('com_weblinks.edit.weblink.id', null);
				$app->setUserState('com_weblinks.edit.weblink.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks', false));
				break;
		}
	}
}