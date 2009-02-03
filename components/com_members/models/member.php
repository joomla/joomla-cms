<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.helper');

/**
 * Member model class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersModelMember extends JModel
{
	/**
	 */
	protected function _populateState($property = null, $default = null)
	{
		// Get the application object.
		$app	= &JFactory::getApplication();
		$user	= &JFactory::getUser();
		$params	= &$app->getParams('com_members');

		// Get the member id.
		$memberId = JRequest::getInt('member_id', $app->getUserState('com_members.edit.profile.id'));
		$memberId = !empty($memberId) ? $memberId : (int)$user->get('id');

		// Set the member id.
		$this->setState('member.id', $memberId);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for members plugins to extend the form with extra fields.
	 *
	 * @param	string	$type	The type of form to load (view, model);
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function &getLoginForm()
	{
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		$form = &JForm::getInstance('jform', 'login', true);

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered login form data.
		$data = $app->getUserState('members.login.form.data', array());

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return'])) {
			$data['return'] = 'index.php?option=com_members&view=profile';
			$app->setUserState('members.login.form.data', $data);
		}

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('member');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareMembersLoginForm', array(&$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return $false;
		}

		return $form;
	}

	/**
	 * Method to validate the profile form data.
	 *
	 * @param	array		$data	The profile form data.
	 * @return	mixed		Array of filtered data if valid, false otherwise.
	 */
	public function validate($data)
	{
		// Get the form.
		$form = &$this->getProfileForm();

		// Check for an error.
		if ($form === false) {
			return false;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		// Check that the passwords match.
		if ($data['password1'] != $data['password2']) {
			$this->setError(new JException(JText::_('MEMBERS_REGISTRATION_PASSWORDS_DO_NOT_MATCH')));
			return false;
		}

		// Check that the e-mail addresses match.
		if ($data['email1'] != $data['email2']) {
			$this->setError(new JException(JText::_('MEMBERS_REGISTRATION_EMAIL_ADDRESSES_DO_NOT_MATCH')));
			return false;
		}

		return $data;
	}

	/**
	 * Method to save a member profile.
	 *
	 * @param	array		$data	The profile form data.
	 * @return	boolean		True on success, false otherwise.
	 */
	public function save($data)
	{
		jimport('joomla.user.helper');
	}
}