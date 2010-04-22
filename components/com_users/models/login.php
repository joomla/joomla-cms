<?php
/**
 * @version
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');
/**
 * Rest model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersModelLogin extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$app	= &JFactory::getApplication();
		$params	= &$app->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @access	public
	 * @param	string	$type	The type of form to load (view, model);
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.0
	 */
	function &getLoginForm()
	{
		// Get the form.
		try {
			$form = $this->getForm('com_users.login', 'login');
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Get the dispatcher and load the users plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareUsersLoginForm', array($this->getState('member.id'), &$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return false;
		}

		// Check the session for previously entered login form data.
		$app = &JFactory::getApplication();
		$data = $app->getUserState('users.login.form.data', array());

		// check for return URL from the request first
		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$data['return'] = base64_decode($return);
			if (!JURI::isInternal($data['return'])) {
				$data['return'] = '';
			}
		}

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return'])) {
			$data['return'] = 'index.php?option=com_users&view=profile';
		}
		$app->setUserState('users.login.form.data', $data);

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}
}

