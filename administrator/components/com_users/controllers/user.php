<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UserControllerUser extends JController
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
		$this->registerTask('unblock',		'block');
	}

	/**
	 * Display the view
	 */
	function display()
	{
		JError::raiseWarning(500, 'This controller does not implement a display method');
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel()
	{
		return parent::getModel('User', 'UserModel', array('ignore_request' => true));
	}

	/**
	 * Method to edit a object
	 *
	 * Sets object ID in the session from the request, checks the item out, and then redirects to the edit page.
	 *
	 * @access	public
	 * @return	void
	 */
	function edit()
	{
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$id  = JRequest::getInt('id', @$cid[0]);

		$session = &JFactory::getSession();
		$session->set('users.user.id', $id);

		if ($id) {
			// Checkout item
			//$model = $this->getModel();
			//$model->checkout($id);
		}
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @access	public
	 * @return	void
	 */
	function cancel()
	{
		$session = &JFactory::getSession();
		// Clear the session of the item
		$session->set('users.user.id', null);

		$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false));
	}

	/**
	 * Save the record
	 */
	function save()
	{
		// Check for request forgeries.
		JRequest::checkToken();

		// Get posted form variables.
		$input = JRequest::get('post');

		// Override the automatic filters
		$input['username']	= JRequest::getVar('username', '', 'post', 'username');
		$input['password']	= JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$input['password2']	= JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!empty($input['password']) AND !empty($input['password2'])) {
			if ($input['password'] !== $input['password2']) {
				$this->setMessage(JText::_('@todo Find string for p[asswords dont match'));
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
				return;
			}
		}

		// Clear static values
		// @todo Look at moving these to the table bind method (but check how new user values are handled)
		unset($input['registerDate']);
		unset($input['lastvisitDate']);
		unset($input['activation']);

		// Get the id of the item out of the session.
		$session	= &JFactory::getSession();
		$id			= (int) $session->get('users.user.id');
		$input['id'] = $id;

		// Get the extensions model and set the post request in its state.
		$task	= $this->getTask();
		$model	= &$this->getModel();
		if (!$model->save($input)) {
			JError::raiseWarning(500, $model->getError());
			$task = 'apply';
			$this->setMessage(JText::_('CANNOT SAVE THE USER INFORMATION'));
		}
		else {
			$this->setMessage(JText::_('Saved'));
		}

		if ($task == 'apply') {
			$session->set('users.redirect.id', $model->getState('id'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
		}
		else if ($task == 'save2new') {
			$session->set('users.user.id', null);
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
		}
		else {
			$session->set('users.user.id', null);
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false));
		}
	}

	/**
	 * Deletes a user
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_('No items selected'));
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

		$this->setRedirect('index.php?option=com_users&view=users');
	}

	/**
	 * Force logout a user
	 *
	 * @request		array	'cid'		An array of ids
	 * @request		mixed	'client'	The client id. If empty, all logout of all clients
	 */
	function logout()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$client = JRequest::getVar('client');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_('No items selected'));
		}
		else {
			if (is_numeric($client)) {
				$options['clientid'][] = $client;
			}
			else {
				// Log the user out of all clients
				$options['clientid'][] = 0;
				$options['clientid'][] = 1;
			}

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			foreach ($cids as $cid) {
				$mainframe->logout($cid, $options);
			}

			$this->setMessage(JText::_('User session ended'));
		}

		$this->setRedirect('index.php?option=com_users&view=users');
	}

	/**
	 * Disables the user account
	 * @todo Move to the other controller - this is just the display controller
	 */
	function block()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db 			=& JFactory::getDBO();
		$acl			=& JFactory::getACL();
		$currentUser 	=& JFactory::getUser();

		$cid 	= JRequest::getVar('cid', array(), '', 'array');
		$block  = ($this->getTask() == 'block') ? 1 : 0;

		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			// @todo Convert to sprintf form
			JError::raiseError(500, JText::_('Select a User to '.$this->getTask(), true));
		}
		foreach ($cid as $id)
		{
			// check for a super admin ... can't delete them
			$objectID 	= $acl->get_object_id('users', $id, 'ARO');
			$groups 	= $acl->get_object_groups($objectID, 'ARO');
			$this_group = strtolower($acl->get_group_name($groups[0], 'ARO'));

			$success = false;
			if ($this_group == 'super administrator') {
				$msg = JText::_('You cannot block a Super Administrator');
			}
			else if ($id == $currentUser->get('id'))
			{
				$msg = JText::_('You cannot block Yourself!');
			}
			else if (($this_group == 'administrator') && ($currentUser->get('gid') == 24))
			{
				$msg = JText::_('WARNBLOCK');
			}
			else
			{
				$user =& JUser::getInstance((int)$id);
				$count = 2;

				if ($user->get('gid') == 25)
				{
					// count number of active super admins
					$query = 'SELECT COUNT(id)'
						. ' FROM #__users'
						. ' WHERE gid = 25'
						. ' AND block = 0'
					;
					$db->setQuery($query);
					$count = $db->loadResult();
				}

				if ($count <= 1 && $user->get('gid') == 25)
				{
					// cannot delete Super Admin where it is the only one that exists
					$msg = "You cannot block this Super Administrator as it is the only active Super Administrator for your site";
				}
				else
				{
					$user =& JUser::getInstance((int)$id);
					$user->block = $block;
					$user->save();

					if($block)
					{
						JRequest::setVar('task', 'block');
						JRequest::setVar('cid', array($id));

						// delete user acounts active sessions
						$this->logout();
					}
				}
			}
		}

		$this->setRedirect('index.php?option=com_users', $msg);
	}

	/**
	 * Method to run batch opterations.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function batch()
	{
		// Get variables from the request.
		$vars	= JRequest::getVar('batch', array(), 'post', 'array');
		$cid	= JRequest::getVar('cid', null, 'post', 'array');

		$model	= &$this->getModel();
		$model->batch($vars, $cid);

		$this->setRedirect('index.php?option=com_users&view=users');
	}
}
