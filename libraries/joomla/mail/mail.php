<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Mail
 */

defined('JPATH_PLATFORM') or die;

jimport('phpmailer.phpmailer');
jimport('joomla.mail.helper');

/**
 * Email Class.  Provides a common interface to send email from the Joomla! Framework
 *
 * @package		Joomla.Platform
 * @subpackage	Mail
 * @since		1.5
 */
class JMail extends PHPMailer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// PHPMailer has an issue using the relative path for it's language files
		$this->SetLanguage('joomla', JPATH_LIBRARIES.'/phpmailer/language/');
	}

	/**
	 * Returns the global email object, only creating it
	 * if it doesn't already exist.
	 *
	 * NOTE: If you need an instance to use that does not have the global configuration
	 * values, use an id string that is not 'Joomla'.
	 *
	 * @param	string	$id		The id string for the JMail instance [optional]
	 *
	 * @return	object	The global JMail object
	 * @since	1.5
	 */
	public static function getInstance($id = 'Joomla')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty($instances[$id])) {
			$instances[$id] = new JMail();
		}

		return $instances[$id];
	}

	/**
	 * Send the mail
	 *
	 * @return	mixed	True if successful, a JError object otherwise
	 * @since	1.5
	 */
	public function Send()
	{
		if (($this->Mailer == 'mail') && ! function_exists('mail')) {
			return JError::raiseNotice(500, JText::_('JLIB_MAIL_FUNCTION_DISABLED'));
		}

		@$result = parent::Send();

		if ($result == false) {
			// TODO: Set an appropriate error number
			$result = JError::raiseNotice(500, JText::_($this->ErrorInfo));
		}

		return $result;
	}

	/**
	 * Set the email sender
	 *
	 * @param	array	email address and Name of sender
	 *		<pre>
	 *			array([0] => email Address [1] => Name)
	 *		</pre>
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function setSender($from)
	{
		if (is_array($from)) {
			// If $from is an array we assume it has an address and a name
			$this->From	= JMailHelper::cleanLine($from[0]);
			$this->FromName = JMailHelper::cleanLine($from[1]);

		}
		elseif (is_string($from)) {
			// If it is a string we assume it is just the address
			$this->From = JMailHelper::cleanLine($from);

		}
		else {
			// If it is neither, we throw a warning
			JError::raiseWarning(0, JText::sprintf('JLIB_MAIL_INVALID_EMAIL_SENDER', $from));
		}

		return $this;
	}

	/**
	 * Set the email subject
	 *
	 * @param	string	$subject	Subject of the email
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function setSubject($subject)
	{
		$this->Subject = JMailHelper::cleanLine($subject);

		return $this;
	}

	/**
	 * Set the email body
	 *
	 * @param	string	$content	Body of the email
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function setBody($content)
	{
		/*
		 * Filter the Body
		 * TODO: Check for XSS
		 */
		$this->Body = JMailHelper::cleanText($content);

		return $this;
	}

	/**
	 * Add recipients to the email
	 *
	 * @param	mixed	$recipient	Either a string or array of strings [email address(es)]
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addRecipient($recipient)
	{
		// If the recipient is an aray, add each recipient... otherwise just add the one
		if (is_array($recipient)) {
			foreach ($recipient as $to)
			{
				$to = JMailHelper::cleanLine($to);
				$this->AddAddress($to);
			}
		}
		else {
			$recipient = JMailHelper::cleanLine($recipient);
			$this->AddAddress($recipient);
		}

		return $this;
	}

	/**
	 * Add carbon copy recipients to the email
	 *
	 * @param	mixed	$cc		Either a string or array of strings [email address(es)]
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addCC($cc)
	{
		// If the carbon copy recipient is an aray, add each recipient... otherwise just add the one
		if (isset ($cc)) {
			if (is_array($cc)) {
				foreach ($cc as $to)
				{
					$to = JMailHelper::cleanLine($to);
					parent::AddCC($to);
				}
			}
			else {
				$cc = JMailHelper::cleanLine($cc);
				parent::AddCC($cc);
			}
		}

		return $this;
	}

	/**
	 * Add blind carbon copy recipients to the email
	 *
	 * @param	mixed	$bcc	Either a string or array of strings [email address(es)]
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addBCC($bcc)
	{
		// If the blind carbon copy recipient is an aray, add each recipient... otherwise just add the one
		if (isset($bcc)) {
			if (is_array($bcc)) {
				foreach ($bcc as $to)
				{
					$to = JMailHelper::cleanLine($to);
					parent::AddBCC($to);
				}
			}
			else {
				$bcc = JMailHelper::cleanLine($bcc);
				parent::AddBCC($bcc);
			}
		}

		return $this;
	}

	/**
	 * Add file attachments to the email
	 *
	 * @param	mixed	$attachment	Either a string or array of strings [filenames]
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addAttachment($attachment)
	{
		// If the file attachments is an aray, add each file... otherwise just add the one
		if (isset($attachment)) {
			if (is_array($attachment)) {
				foreach ($attachment as $file)
				{
					parent::AddAttachment($file);
				}
			}
			else {
				parent::AddAttachment($attachment);
			}
		}

		return $this;
	}

	/**
	 * Add Reply to email address(es) to the email
	 *
	 * @param	array	$replyto	Either an array or multi-array of form
	 *		<pre>
	 *			array([0] => email Address [1] => Name)
	 *		</pre>
	 *
	 * @return	JMail	Returns this object for chaining.
	 * @since	1.5
	 */
	public function addReplyTo($replyto)
	{
		// Take care of reply email addresses
		if (is_array($replyto[0])) {
			foreach ($replyto as $to)
			{
				$to0 = JMailHelper::cleanLine($to[0]);
				$to1 = JMailHelper::cleanLine($to[1]);
				parent::AddReplyTo($to0, $to1);
			}
		}
		else {
			$replyto0 = JMailHelper::cleanLine($replyto[0]);
			$replyto1 = JMailHelper::cleanLine($replyto[1]);
			parent::AddReplyTo($replyto0, $replyto1);
		}

		return $this;
	}

	/**
	 * Use sendmail for sending the email
	 *
	 * @param	string	$sendmail	Path to sendmail [optional]
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function useSendmail($sendmail = null)
	{
		$this->Sendmail = $sendmail;

		if (!empty ($this->Sendmail)) {
			$this->IsSendmail();

			return true;
		}
		else {
			$this->IsMail();

			return false;
		}
	}

	/**
	 * Use SMTP for sending the email
	 *
	 * @param	string	$auth	SMTP Authentication [optional]
	 * @param	string	$host	SMTP Host [optional]
	 * @param	string	$user	SMTP Username [optional]
	 * @param	string	$pass	SMTP Password [optional]
	 * @param			$secure
	 * @param	int		$port
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function useSMTP($auth = null, $host = null, $user = null, $pass = null, $secure = null, $port = 25)
	{
		$this->SMTPAuth = $auth;
		$this->Host		= $host;
		$this->Username = $user;
		$this->Password = $pass;
		$this->Port		= $port;

		if ($secure == 'ssl' || $secure == 'tls') {
			$this->SMTPSecure = $secure;
		}

		if (($this->SMTPAuth !== null && $this->Host !== null && $this->Username !== null && $this->Password !== null)
			|| ($this->SMTPAuth === null && $this->Host !== null)) {
			$this->IsSMTP();

			return true;
		}
		else {
			$this->IsMail();

			return false;
		}
	}

	/**
	 * Function to send an email
	 *
	 * @param	string	$from			From email address
	 * @param	string	$fromName		From name
	 * @param	mixed	$recipient		Recipient email address(es)
	 * @param	string	$subject		email subject
	 * @param	string	$body			Message body
	 * @param	boolean	$mode			false = plain text, true = HTML
	 * @param	mixed	$cc				CC email address(es)
	 * @param	mixed	$bcc			BCC email address(es)
	 * @param	mixed	$attachment		Attachment file name(s)
	 * @param	mixed	$replyTo		Reply to email address(es)
	 * @param	mixed	$replyToName	Reply to name(s)
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function sendMail($from, $fromName, $recipient, $subject, $body, $mode=0,
		$cc=null, $bcc=null, $attachment=null, $replyTo=null, $replyToName=null)
	{
		$this->setSender(array($from, $fromName));
		$this->setSubject($subject);
		$this->setBody($body);

		// Are we sending the email as HTML?
		if ($mode) {
			$this->IsHTML(true);
		}

		$this->addRecipient($recipient);
		$this->addCC($cc);
		$this->addBCC($bcc);
		$this->addAttachment($attachment);

		// Take care of reply email addresses
		if (is_array($replyTo)) {
			$numReplyTo = count($replyTo);

			for ($i = 0; $i < $numReplyTo; $i++)
			{
				$this->addReplyTo(array($replyTo[$i], $replyToName[$i]));
			}
		}
		else if (isset($replyTo)) {
			$this->addReplyTo(array($replyTo, $replyToName));
		}

		return  $this->Send();
	}

	/**
	 * Sends mail to administrator for approval of a user submission
	 *
	 * @param	string	$adminName	Name of administrator
	 * @param	string	$adminEmail	Email address of administrator
	 * @param	string	$email		[NOT USED TODO: Deprecate?]
	 * @param	string	$type		Type of item to approve
	 * @param	string	$title		Title of item to approve
	 * @param	string	$author		Author of item to approve
	 * @param	string	$url
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function sendAdminMail($adminName, $adminEmail, $email, $type, $title, $author, $url = null)
	{
		$subject = JText::sprintf('JLIB_MAIL_USER_SUBMITTED', $type);

		$message = sprintf (JText::_('JLIB_MAIL_MSG_ADMIN'), $adminName, $type, $title, $author, $url, $url, 'administrator', $type);
		$message .= JText::_('JLIB_MAIL_MSG') ."\n";

		$this->addRecipient($adminEmail);
		$this->setSubject($subject);
		$this->setBody($message);

		return $this->Send();
	}
}