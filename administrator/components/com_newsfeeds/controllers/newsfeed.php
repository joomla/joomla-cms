<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * Newsfeed controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @version		1.6
 */
class NewsfeedsControllerNewsfeed extends JController
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
		$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds', false));
	}

	/**
	 * Method to add a new newsfeed.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialize variables.
		$app = &JFactory::getApplication();

		// Clear the level edit information from the session.
		$app->setUserState('com_newsfeeds.edit.newsfeed.id', null);
		$app->setUserState('com_newsfeeds.edit.newsfeed.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds&view=newsfeed&layout=edit', false));
	}

	/**
	 * Method to edit an existing newsfeed.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Newsfeed', 'NewsfeedsModel');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Get the previous newsfeed id (if any) and the current newsfeed id.
		$previousId		= (int) $app->getUserState('com_newsfeeds.edit.newsfeed.id');
		$newsfeedId		= (int) (count($cid) ? $cid[0] : JRequest::getInt('newsfeed_id'));

		// If newsfeed ids do not match, checkin previous newsfeed.
		if (($previousId > 0) && ($newsfeedId != $previousId)) {
			if (!$model->checkin($previousId)) {
				// Check-in failed, go back to the newsfeed and display a notice.
				$message = JText::sprintf('JError_Checkin_failed', $model->getError());
				$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeed&layout=edit', $message, 'error');
				return false;
			}
		}

		// Attempt to check-out the new newsfeed for editing and redirect.
		if (!$model->checkout($newsfeedId)) {
			// Check-out failed, go back to the list and display a notice.
			$message = JText::sprintf('JError_Checkout_failed', $model->getError());
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeed&newsfeed_id='.$newsfeedId, $message, 'error');
			return false;
		}
		else {
			// Check-out succeeded, push the new newsfeed id into the session.
			$app->setUserState('com_newsfeeds.edit.newsfeed.id',	$newsfeedId);
			$app->setUserState('com_newsfeeds.edit.newsfeed.data', null);
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeed&layout=edit');
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
		$model	= &$this->getModel('Newsfeed', 'NewsfeedsModel');

		// Get the newsfeed id.
		$newsfeedId = (int) $app->getUserState('com_newsfeeds.edit.newsfeed.id');

		// Attempt to check-in the current newsfeed.
		if ($newsfeedId) {
			if (!$model->checkin($newsfeedId)) {
				// Check-in failed, go back to the newsfeed and display a notice.
				$message = JText::sprintf('JError_Checkin_failed', $model->getError());
				$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeed&layout=edit&hidemainmenu=1', $message, 'error');
				return false;
			}
		}

		// Clean the session data and redirect.
		$app->setUserState('com_newsfeeds.edit.newsfeed.id',		null);
		$app->setUserState('com_newsfeeds.edit.newsfeed.data',	null);
		$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds&view=newsfeeds', false));
	}

	/**
	 * Method to save a newsfeed.
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
		$model	= $this->getModel('Newsfeed');
		$form	= &$model->getForm();
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false) {
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
			$app->setUserState('com_newsfeeds.edit.newsfeed.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds&view=newsfeed&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Attempt to save the newsfeed.
		$return = $model->save($data);

		if ($return === false) {
			// Save failed, go back to the newsfeed and display a notice.
			$message = JText::sprintf('JError_Save_failed', $model->getError());
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeed&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		// Save succeeded, check-in the newsfeed.
		if (!$model->checkin()) {
			// Check-in failed, go back to the newsfeed and display a notice.
			$message = JText::sprintf('JError_Checkin_saved', $model->getError());
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeed&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		$this->setMessage(JText::_('JController_Save_success'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task) {
			case 'apply':
				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds&view=newsfeed&layout=edit', false));
				break;

			case 'save2new':
				// Clear the member id and data from the session.
				$app->setUserState('com_newsfeeds.edit.newsfeed.id', null);
				$app->setUserState('com_newsfeeds.edit.newsfeed.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds&view=newsfeed&layout=edit', false));
				break;

			default:
				// Clear the member id and data from the session.
				$app->setUserState('com_newsfeeds.edit.newsfeed.id', null);
				$app->setUserState('com_newsfeeds.edit.newsfeed.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_newsfeeds&view=newsfeeds', false));
				break;
		}
	}
}