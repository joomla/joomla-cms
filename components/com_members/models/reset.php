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
 * Reset model class for JXtended Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersModelReset extends JModel
{
	/**
	 * Method to get the password reset request form.
	 *
	 * @return	mixed	JXForm object on success, JException on failure.
	 */
	public function &getRequestForm()
	{
		return $this->_getForm('reset_request', 'onPrepareMembersResetRequestForm');
	}

	/**
	 * Method to get the password reset confirm form.
	 *
	 * @return	mixed	JXForm object on success, JException on failure.
	 */
	public function &getConfirmForm()
	{
		return $this->_getForm('reset_confirm', 'onPrepareMembersResetConfirmForm');
	}

	/**
	 * Method to get the password reset complete form.
	 *
	 * @return	mixed	JXForm object on success, JException on failure.
	 */
	public function &getCompleteForm()
	{
		return $this->_getForm('reset_complete', 'onPrepareMembersResetCompleteForm');
	}

	/**
	 * Method to get the appropriate form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for members plugins to extend the form with extra fields.
	 *
	 * @param	string	$file	The form file to load.
	 * @param	string	$event	The form event to trigger.
	 * @return	mixed	JXForm object on success, JException on failure.
	 */
	protected function &_getForm($file, $event)
	{
		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		$form = &JForm::getInstance($file);

		// Check for an error.
		if (JError::isError($form)) {
			$error = new JException($form->getMessage());
			return $error;
		}

		// Get the dispatcher and load the members plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('member');

		// Trigger the form event.
		$results = $dispatcher->trigger($event, array(&$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$error = new JException($dispatcher->getError());
			return $error;
		}

		return $form;
	}

	/**
	 * Method to start the password reset process.
	 */
	function request($data)
	{
		// Get the form.
		$form = &$this->getRequestForm();

		// Check for an error.
		if ($form === false) {
			return $form;
		}

		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			return $form;
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

		// Get the user id.
		jimport('joomla.user.helper');
		$userId	= JUserHelper::getUserId($data['username']);

		// Make sure the user exists.
		if (empty($userId)) {
			$this->setError(JText::_('MEMBERS_RESET_USER_NOT_FOUND'));
			return false;
		}

		// Get the user object.
		$user = JUser::getInstance($userId);

		// Make sure the user isn't blocked.
		if ($user->block) {
			$this->setError(JText::_('MEMBERS_RESET_USER_BLOCKED'));
			return false;
		}

		// Set the confirmation token.
		$token = JUtility::getHash(JUserHelper::genRandomPassword());
		$user->activation = $token;

		// Save the user to the database.
		if (!$user->save(true)) {
			return new JException($user->getError());
		}

		// Send the token to the user via e-mail
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_members'.DS.'tables');

		// Instantiate the email template objects.
		jimport('joomla.utilities.simpletemplate');
		$templateTable	= &JTable::getInstance('Template', 'MembersTable');
		$mailTemplate	= new JSimpleTemplate;

		// Push the table in.
		$mailTemplate->set('table', $templateTable);

		if (!$mailTemplate->loadFromDB('registration.reset')) {
			// TODO: Log error.
		}

		// Push in a few extra details.
		$user->password_reset_link	= JRoute::_('index.php?option=com_members&task=account.validateResetToken&username='.$user->username.'&token='.$token, false, -1);
		$user->token = $token;

		$query = 'SELECT *' .
				' FROM #__jxmembers_members' .
				' WHERE user_id = '. (int) $user->id;
		$this->_db->setQuery($query);
		$m = $this->_db->loadObject();

		$user->fname = $m->fName;
		$user->lname = $m->lName;

		// Push in the email data.
		$mailTemplate->mergeObject($user);

		// Get the email data.
		$config		= &JFactory::getConfig();
		$fromEmail	= $config->getValue('config.mailfrom');
		$fromName	= $config->getValue('config.fromname');
		$toEmail		= $user->email;
		$subject		= $mailTemplate->getTitle();
		$message	= $mailTemplate->getBody();

		jimport('joomla.mail.helper');
		if (!JUtility::sendMail($fromEmail, $fromName, $toEmail, $subject, $message)) {
			// TODO: log error.
			return false;
		}

		return true;
	}
}