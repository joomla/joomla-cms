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

/**
 * Remind model class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */

class UsersModelRemind extends JModelForm
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
	 * Method to get the username remind request form.
	 *
	 * @return	object	JForm object on success, JException on failure.
	 * @since	1.0
	 */
	function &getForm()
	{
		// Get the form.
		$form = parent::getForm('com_users.remind', 'remind', array('control' => 'jform'));
		if (empty($form)) {
			return false;
		}

		// Get the dispatcher and load the users plugins.
		$dispatcher	= &JDispatcher::getInstance();
		JPluginHelper::importPlugin('users');

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onPrepareUserRemindForm', array(&$form));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			$this->setError($dispatcher->getError());
			return false;
		}

		return $form;

	}
	function processRemindRequest($data)
	{
		// Get the form.
		$form = &$this->getRemindForm();

		// Check for an error.
		if (JError::isError($form)) {
			return $form;
		}

		// Validate the data.
		$data = $this->validate($form, $data);

		// Check the validator results.
		if (JError::isError($data) || $data === false) {
			return $data;
		}

		// Find the user id for the given e-mail address.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('*');
		$query->from('`#__users`');
		$query->where('`email` = '.$db->Quote($data['email']));

		// Get the user id.
		$db->setQuery((string) $query);
		$user = $db->loadObject();

		// Check for an error.
		if ($db->getErrorNum()) {
			return new JException(JText::sprintf('COM_USERS_DATABASE_ERROR', $db->getErrorMsg()), 500);
		}

		// Check for a user.
		if (empty($user)) {
			$this->setError(JText::_('COM_USERS_USER_NOT_FOUND'));
			return false;
		}

		// Make sure the user isn't blocked.
		if ($user->block) {
			$this->setError(JText::_('COM_USERS_USER_BLOCKED'));
			return false;
		}

		$config	= &JFactory::getConfig();

		// Assemble the login link.
		$itemid = UsersHelperRoute::getLoginRoute();
		$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
		$link	= 'index.php?option=com_users&view=login'.$itemid;
		$mode	= $config->get('force_ssl', 0) == 2 ? 1 : -1;

		// Put together the e-mail template data.
		$data = JArrayHelper::fromObject($user);
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['link_text']	= JRoute::_($link, false, $mode);
		$data['link_html']	= JRoute::_($link, true, $mode);

		// Load the mail template.
		jimport('joomla.utilities.simpletemplate');
		$template = new JSimpleTemplate();

		if (!$template->load('users.username.remind.request')) {
			return new JException(JText::_('COM_USERS_REMIND_MAIL_TEMPLATE_NOT_FOUND'), 500);
		}

		// Push in the email template variables.
		$template->bind($data);

		// Get the email information.
		$toEmail	= $user->email;
		$subject	= $template->getTitle();
		$message	= $template->getHtml();

		// Send the password reset request e-mail.
		$return = JUtility::sendMail($data['mailfrom'], $data['fromname'], $toEmail, $subject, $message);

		// Check for an error.
		if ($return !== true) {
			return new JException(JText::_('COM_USERS_MAIL_FAILED'), 500);
		}

		return true;
	}


}