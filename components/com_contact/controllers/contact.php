<?php
/**
 * @version
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Site
 * @subpackage	com_content
 */
class ContactControllerContact extends JController
{
	/**
	 * Method to send an email to a contact
	 *
	 * @static
	 * @since 1.0
	 */
	function submit()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise some variables
		$app		= JFactory::getApplication();
		$db			= JFactory::getDbo();
		$SiteName	= $app->getCfg('sitename');

		$default	= JText::sprintf('MAILENQUIRY', $SiteName);
		$contactId	= JRequest::getInt('id',			0,			'post');
		$name		= JRequest::getVar('name',			'',			'post');
		$email		= JRequest::getVar('email',			'',			'post');
		$subject	= JRequest::getVar('subject',		$default,	'post');
		$body		= JRequest::getVar('text',			'',			'post');
		$emailCopy	= JRequest::getInt('email_copy',	0,			'post');

		// load the contact details
		$model		= $this->getModel('contact');

		// query options
		$contact		= $model->getItem($contactId);

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		/*
		 * If there is no valid email address or message body then we throw an
		 * error and return false.
		 */
		jimport('joomla.mail.helper');
		if (!$email || !$body || (JMailHelper::isEmailAddress($email) == false))
		{
			$this->setError(JText::_('COM_CONTACT_FORM_NC'));
			$this->display();
			return false;
		}

		// Contact plugins
		JPluginHelper::importPlugin('contact');
		$dispatcher	= JDispatcher::getInstance();

		// Input validation
		if  (!$this->_validateInputs($contact, $email, $subject, $body)) {
			JError::raiseWarning(0, $this->getError());
			return false;
		}

		// Custom handlers
		$post		= JRequest::get('post');
		$results	= $dispatcher->trigger('onValidateContact', array(&$contact, &$post));

		foreach ($results as $result)
		{
			if (JError::isError($result)) {
				return false;
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$results	= $dispatcher->trigger('onSubmitContact', array(&$contact, &$post));

		$pparams = $app->getParams('com_contact');
		if (!$pparams->get('custom_reply'))
		{
			$MailFrom	= $app->getCfg('mailfrom');
			$FromName	= $app->getCfg('fromname');

			// Prepare email body
			$prefix = JText::sprintf('COM_CONTACT_ENQUIRY_TEXT', JURI::base());
			$body	= $prefix."\n".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($body);

			$mail = JFactory::getMailer();

			$mail->addRecipient($contact->email_to);
			$mail->setSender(array($email, $name));
			$mail->setSubject($FromName.': '.$subject);
			$mail->setBody($body);

			$sent = $mail->Send();

			/*
			 * If we are supposed to copy the admin, do so.
			 */
			// parameter check
			$params = new JRegistry;
			$params->loadJSON($contact->params);
			$emailcopyCheck = $params->get('show_email_copy', 0);

			// check whether email copy function activated
			if ($emailCopy && $emailcopyCheck)
			{
				$copyText		= JText::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $SiteName);
				$copyText		.= "\r\n\r\n".$body;
				$copySubject	= JText::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

				$mail = JFactory::getMailer();

				$mail->addRecipient($email);
				$mail->setSender(array($MailFrom, $FromName));
				$mail->setSubject($copySubject);
				$mail->setBody($copyText);

				$sent = $mail->Send();
			}
		}

		if (!JError::isError($sent)) {
			$msg = JText::_('COM_CONTACT_EMAIL_THANKS');
		}

		//redirect if it is set
		if ($contact->params->get('redirect'))
		{
			$link=$contact->params->get('redirect');
		}
		else
		{
			// stay on the same  contact page
			$link = JRoute::_('index.php?option=com_contact&view=contact&id='.(int) $contact->id, false);
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Validates some inputs based on component configuration
	 *
	 * @param Object	$contact	JTable Object
	 * @param String	$email		Email address
	 * @param String	$subject	Email subject
	 * @param String	$body		Email body
	 * @return Boolean
	 * @access protected
	 * @since 1.5
	 */
	function _validateInputs($contact, $email, $subject, $body)
	{
		$app	= JFactory::getApplication();
		$session = JFactory::getSession();

		// Get params and component configurations
		$params = new JRegistry;
		$params->loadJSON($contact->params);
		$pparams	= $app->getParams('com_contact');

		// check for session cookie
		$sessionCheck	= $pparams->get('validate_session', 1);
		$sessionName	= $session->getName();
		if  ($sessionCheck) {
			if (!isset($_COOKIE[$sessionName])) {
				$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
		}

		// Determine banned emails
		$configEmail	= $pparams->get('banned_email', '');
		$paramsEmail	= $params->get('banned_mail', '');
		$bannedEmail	= $configEmail . ($paramsEmail ? ';'.$paramsEmail : '');

		// Prevent form submission if one of the banned text is discovered in the email field
		if (false === $this->_checkText($email, $bannedEmail)) {
			$this->setError(JText::sprintf('COM_CONTACT_EMAIL_BANNEDTEXT', JText::_('JGLOBAL_EMAIL')));
			return false;
		}

		// Determine banned subjects
		$configSubject	= $pparams->get('banned_subject', '');
		$paramsSubject	= $params->get('banned_subject', '');
		$bannedSubject	= $configSubject . ($paramsSubject ? ';'.$paramsSubject : '');

		// Prevent form submission if one of the banned text is discovered in the subject field
		if (false === $this->_checkText($subject, $bannedSubject)) {
			$this->setError(JText::sprintf('COM_CONTACT_EMAIL_BANNEDTEXT',JText::_('COM_CONTACT_CONTACT_MESSAGE_SUBJECT')));
			return false;
		}

		// Determine banned Text
		$configText		= $pparams->get('banned_text', '');
		$paramsText		= $params->get('banned_text', '');
		$bannedText	= $configText . ($paramsText ? ';'.$paramsText : '');

		// Prevent form submission if one of the banned text is discovered in the text field
		if (false === $this->_checkText($body, $bannedText)) {
			$this->setError(JText::sprintf('COM_CONTACT_EMAIL_BANNEDTEXT', JText::_('COM_CONTACT_CONTACT_ENTER_MESSAGE')));
			return false;
		}

		// test to ensure that only one email address is entered
		$check = explode('@', $email);
		if (strpos($email, ';') || strpos($email, ',') || strpos($email, ' ') || count($check) > 2) {
			$this->setError(JText::_('COM_CONTACT_NOT_MORE_THAN_ONE_EMAIL_ADDRESS', true));
			return false;
		}

		return true;
	}

	/**
	 * Checks $text for values contained in the array $array, and sets error message if true...
	 *
	 * @param String	$text		Text to search against
	 * @param String	$list		semicolon (;) seperated list of banned values
	 * @return Boolean
	 * @access protected
	 * @since 1.5.4
	 */
	function _checkText($text, $list) {
		if (empty($list) || empty($text)) return true;
		$array = explode(';', $list);
		foreach ($array as $value) {
			$value = trim($value);
			if (empty($value)) continue;
			if (JString::stristr($text, $value) !== false) {
				return false;
			}
		}
		return true;
	}
}
