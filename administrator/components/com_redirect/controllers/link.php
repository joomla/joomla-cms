<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.controller');

/**
 * Link controller class for Redirect.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectControllerLink extends JController
{
	/**
	 * Overridden constructor to register alternate tasks
	 *
	 * @access	protected
	 * @return	void
	 * @since	1.6
	 */
	function __construct()
	{
		parent::__construct();

		// Map the save tasks.
		$this->registerTask('save2new',		'save');
		$this->registerTask('apply',		'save');

		// Map the publishing state tasks.
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('archive',		'publish');
	}

	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function display()
	{
		$this->setRedirect('index.php?option=com_redirect');
	}

	/**
	 * Method to add a new redirect link.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function add()
	{
		// Initialize variables.
		$app = & JFactory::getApplication();

		// Clear the link id from the session.
		$app->setUserState('redirect.edit.link.id', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=link&layout=edit&hidemainmenu=1', false));
	}

	/**
	 * Method to setup a redirect link for editing and redirect to the edit form.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function edit()
	{
		// Initialize variables.
		$app	= & JFactory::getApplication();
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		// Get the previous link id (if any) and the current link id.
		$previousId = (int) $app->getUserState('redirect.edit.link.id');
		$linkId		= (int) (count($cid) ? $cid[0] : JRequest::getInt('l_id'));

		// Set the category id for the category to edit in the session.
		$app->setUserState('redirect.edit.link.id', $linkId);

		// Get the model.
		$model = & $this->getModel('Link', 'RedirectModel');

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=link&layout=edit&hidemainmenu=1', false));
	}

	/**
	 * Method to cancel an edit
	 *
	 * Sets item id in the session to null, and then redirects to the list screen.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function cancel()
	{
		// Initialize variables.
		$app = & JFactory::getApplication();

		// Clear the link id from the session.
		$app->setUserState('redirect.edit.link.id', null);

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=links', false));
	}

	/**
	 * Method to delete redirect links.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get and sanitize the items to delete.
		$cid = JRequest::getVar('cid', null, 'post', 'array');
		JArrayHelper::toInteger($cid);

		// Get the model.
		$model = & $this->getModel('Link', 'RedirectModel');

		// Attempt to delete the item(s).
		if (!$model->delete($cid)) {
			$this->setMessage(JText::sprintf('Redirect_Link_Delete_Failed', $model->getError()), 'notice');
		}
		else {
			$this->setMessage(JText::sprintf('Redirect_Link_Delete_Success', count($cid)));
		}

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=links', false));
	}

	/**
	 * Method to save a redirect link.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Initialize variables.
		$app = & JFactory::getApplication();

		// Get the posted values from the request.
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('redirect.edit.link.id');

		// Get the model and attempt to validate the posted data.
		$model  = & $this->getModel('Link', 'RedirectModel');
		$return	= $model->validate($data);

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('redirect.edit.link.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=link&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('redirect.edit.link.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('Redirect_Link_Save_Failed', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=link&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task)
		{
			case 'apply':
				// Redirect back to the edit screen.
				$this->setMessage(JText::_('Redirect_Link_Save_Success'));
				$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=link&layout=edit&hidemainmenu=1', false));
				break;

			case 'save2new':
				// Clear the link id from the session.
				$app->setUserState('redirect.edit.link.id', null);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('Redirect_Link_Save_Success'));
				$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=link&layout=edit&hidemainmenu=1', false));
				break;

			default:
				// Clear the link id from the session.
				$app->setUserState('redirect.edit.link.id', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('Redirect_Link_Save_Success'));
				$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=links', false));
				break;
		}

		// Flush the data from the session.
		$app->setUserState('redirect.edit.link.data', null);
	}

	/**
	 * Method to activate a list of links
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function activate()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get and sanitize the items to delete.
		$cid = JRequest::getVar('cid', null, 'post', 'array');
		JArrayHelper::toInteger($cid);

		// Get the model.
		$model = & $this->getModel('Link', 'RedirectModel');

		$new_url = JRequest::getString('new_url');
		$comment = JRequest::getString('comment');

		// Attempt to activate the links
		$result = $model->activate($cid, $new_url, $comment);

		// If there is a problem, set the error message.
		if (!$result) {
			$this->setMessage($model->getError());
		}

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=links', false));
	}

	/**
	 * Method to set the published state of links.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.6
	 */
	function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get and sanitize the items to delete.
		$cid = JRequest::getVar('cid', null, 'post', 'array');
		JArrayHelper::toInteger($cid);

		// Get the model.
		$model = &$this->getModel('Link', 'RedirectModel');

		// Attempt to set the publishing state for the links.
		switch ($this->_task)
		{
			case 'publish':
				$result = $model->publish($cid);
				break;

			case 'unpublish':
				$result = $model->unpublish($cid);
				break;

			case 'archive':
				$result = $model->archive($cid);
				break;
		}

		// If there is a problem, set the error message.
		if (!$result) {
			$this->setMessage($model->getError());
		}

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_redirect&view=links', false));
	}
}
