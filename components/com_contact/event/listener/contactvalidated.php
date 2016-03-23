<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Domain event listener for Contact Validated events.
 * 
 * @since  __DEPLOY_VERSION__
 */
final class ContactEventListenerContactvalidated
{
	/**
	 * Event listener.
	 * 
	 * This event is fired only for a previously validated contact.
	 * This method sends an email.
	 * 
	 * @param   ContactEventContactsucceeded  $event  A domain event.
	 * 
	 * @return  void
	 */
	public static function onContactEventContactvalidated(ContactEventContactValidated $event)
	{
		$sent = false;
		$data = $event->data;
		$contact = $event->contact;
		$params = $event->contact->params;

		if (!$params->get('custom_reply'))
		{
			$sent = self::sendEmail($data, $contact, $params->get('show_email_copy'));
		}

		// If the mailer returns an exception, throw it properly.
		if (($sent instanceof Exception))
		{
			throw $sent;
		}
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   array     $data                  The data to send in the email.
	 * @param   stdClass  $contact               The user information to send the email to
	 * @param   boolean   $copy_email_activated  True to send a copy of the email to the user.
	 *
	 * @return  boolean  True on success sending the email, false on failure.
	 *
	 * @since   1.6.4
	 */
	private static function sendEmail($data, $contact, $copy_email_activated)
	{
		$app = JFactory::getApplication();

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user      = JUser::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');

		$name    = $data['contact_name'];
		$email   = JStringPunycode::emailToPunycode($data['contact_email']);
		$subject = $data['contact_subject'];
		$body    = $data['contact_message'];

		// Prepare email body
		$prefix = JText::sprintf('COM_CONTACT_ENQUIRY_TEXT', JUri::base());
		$body   = $prefix . "\n" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		$mail = JFactory::getMailer();
		$mail->addRecipient($contact->email_to);
		$mail->addReplyTo($email, $name);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->setBody($body);
		$sent = $mail->Send();

		// If we are supposed to copy the sender, do so.

		// Check whether email copy function activated
		if ($copy_email_activated == true && !empty($data['contact_email_copy']))
		{
			$copytext    = JText::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $sitename);
			$copytext    .= "\r\n\r\n" . $body;
			$copysubject = JText::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

			$mail = JFactory::getMailer();
			$mail->addRecipient($email);
			$mail->addReplyTo(array($email, $name));
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		return $sent;
	}
}
