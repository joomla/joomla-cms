<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Mail
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Email Class.  Provides a common interface to send email from the Joomla! Platform
 *
 * @since  11.1
 */
class JMail extends PHPMailer
{
	/**
	 * JMail instances container.
	 *
	 * @var    JMail[]
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Charset of the message.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $CharSet = 'utf-8';

	/**
	 * Constructor
	 *
	 * @param   boolean  $exceptions  Flag if Exceptions should be thrown
	 *
	 * @since   11.1
	 */
	public function __construct($exceptions = true)
	{
		parent::__construct($exceptions);

		// PHPMailer has an issue using the relative path for its language files
		$this->setLanguage('joomla', __DIR__ . '/language');

		// Configure a callback function to handle errors when $this->edebug() is called
		$this->Debugoutput = function ($message, $level)
		{
			JLog::add(sprintf('Error in JMail API: %s', $message), JLog::ERROR, 'mail');
		};

		// If debug mode is enabled then set SMTPDebug to the maximum level
		if (defined('JDEBUG') && JDEBUG)
		{
			$this->SMTPDebug = 4;
		}
	}

	/**
	 * Returns the global email object, only creating it if it doesn't already exist.
	 *
	 * NOTE: If you need an instance to use that does not have the global configuration
	 * values, use an id string that is not 'Joomla'.
	 *
	 * @param   string   $id          The id string for the JMail instance [optional]
	 * @param   boolean  $exceptions  Flag if Exceptions should be thrown [optional]
	 *
	 * @return  JMail  The global JMail object
	 *
	 * @since   11.1
	 */
	public static function getInstance($id = 'Joomla', $exceptions = true)
	{
		if (empty(self::$instances[$id]))
		{
			self::$instances[$id] = new JMail($exceptions);
		}

		return self::$instances[$id];
	}

	/**
	 * Send the mail
	 *
	 * @return  boolean|JException  Boolean true if successful, boolean false if the `mailonline` configuration is set to 0,
	 *                              or a JException object if the mail function does not exist or sending the message fails.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function Send()
	{
		if (JFactory::getConfig()->get('mailonline', 1))
		{
			if (($this->Mailer == 'mail') && !function_exists('mail'))
			{
				return JError::raiseNotice(500, JText::_('JLIB_MAIL_FUNCTION_DISABLED'));
			}

			try
			{
				// Try sending with default settings
				$result = parent::send();
			}
			catch (phpmailerException $e)
			{
				$result = false;

				if ($this->SMTPAutoTLS)
				{
					/**
					 * PHPMailer has an issue with servers with invalid certificates
					 *
					 * See: https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting#opportunistic-tls
					 */
					$this->SMTPAutoTLS = false;

					try
					{
						// Try it again with TLS turned off
						$result = parent::send();
					}
					catch (phpmailerException $e)
					{
						// Keep false for B/C compatibility
						$result = false;
					}
				}
			}

			if ($result == false)
			{
				$result = JError::raiseNotice(500, JText::_($this->ErrorInfo));
			}

			return $result;
		}

		JFactory::getApplication()->enqueueMessage(JText::_('JLIB_MAIL_FUNCTION_OFFLINE'));

		return false;
	}

	/**
	 * Set the From and FromName properties.
	 *
	 * @param   string   $address  The sender email address
	 * @param   string   $name     The sender name
	 * @param   boolean  $auto     Whether to also set the Sender address, defaults to true
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function setFrom($address, $name = '', $auto = true)
	{
		try
		{
			if (parent::setFrom($address, $name, $auto) === false)
			{
				return false;
			}
		}
		catch (phpmailerException $e)
		{
			// The parent method will have already called the logging callback, just log our deprecated error handling message
			JLog::add(__METHOD__ . '() will not catch phpmailerException objects as of 4.0.', JLog::WARNING, 'deprecated');

			return false;
		}
	}

	/**
	 * Set the email sender
	 *
	 * @param   mixed  $from  email address and Name of sender
	 *                        <code>array([0] => email Address, [1] => Name)</code>
	 *                        or as a string
	 *
	 * @return  JMail|boolean  Returns this object for chaining on success or boolean false on failure.
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public function setSender($from)
	{
		// Wrapped in try/catch if PHPMailer is configured to throw exceptions
		try
		{
			if (is_array($from))
			{
				// If $from is an array we assume it has an address and a name
				if (isset($from[2]))
				{
					// If it is an array with entries, use them
					$result = $this->setFrom(JMailHelper::cleanLine($from[0]), JMailHelper::cleanLine($from[1]), (bool) $from[2]);
				}
				else
				{
					$result = $this->setFrom(JMailHelper::cleanLine($from[0]), JMailHelper::cleanLine($from[1]));
				}
			}
			elseif (is_string($from))
			{
				// If it is a string we assume it is just the address
				$result = $this->setFrom(JMailHelper::cleanLine($from));
			}
			else
			{
				// If it is neither, we log a message and throw an exception
				JLog::add(JText::sprintf('JLIB_MAIL_INVALID_EMAIL_SENDER', $from), JLog::WARNING, 'jerror');

				throw new UnexpectedValueException(sprintf('Invalid email Sender: %s, JMail::setSender(%s)', $from));
			}

			// Check for boolean false return if exception handling is disabled
			if ($result === false)
			{
				return false;
			}
		}
		catch (phpmailerException $e)
		{
			// The parent method will have already called the logging callback, just log our deprecated error handling message
			JLog::add(__METHOD__ . '() will not catch phpmailerException objects as of 4.0.', JLog::WARNING, 'deprecated');

			return false;
		}

		return $this;
	}

	/**
	 * Set the email subject
	 *
	 * @param   string  $subject  Subject of the email
	 *
	 * @return  JMail  Returns this object for chaining.
	 *
	 * @since   11.1
	 */
	public function setSubject($subject)
	{
		$this->Subject = JMailHelper::cleanLine($subject);

		return $this;
	}

	/**
	 * Set the email body
	 *
	 * @param   string  $content  Body of the email
	 *
	 * @return  JMail  Returns this object for chaining.
	 *
	 * @since   11.1
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
	 * Add recipients to the email.
	 *
	 * @param   mixed   $recipient  Either a string or array of strings [email address(es)]
	 * @param   mixed   $name       Either a string or array of strings [name(s)]
	 * @param   string  $method     The parent method's name.
	 *
	 * @return  JMail|boolean  Returns this object for chaining on success or boolean false on failure.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	protected function add($recipient, $name = '', $method = 'addAddress')
	{
		$method = lcfirst($method);

		// If the recipient is an array, add each recipient... otherwise just add the one
		if (is_array($recipient))
		{
			if (is_array($name))
			{
				$combined = array_combine($recipient, $name);

				if ($combined === false)
				{
					throw new InvalidArgumentException("The number of elements for each array isn't equal.");
				}

				foreach ($combined as $recipientEmail => $recipientName)
				{
					$recipientEmail = JMailHelper::cleanLine($recipientEmail);
					$recipientName = JMailHelper::cleanLine($recipientName);

					// Wrapped in try/catch if PHPMailer is configured to throw exceptions
					try
					{
						// Check for boolean false return if exception handling is disabled
						if (call_user_func('parent::' . $method, $recipientEmail, $recipientName) === false)
						{
							return false;
						}
					}
					catch (phpmailerException $e)
					{
						// The parent method will have already called the logging callback, just log our deprecated error handling message
						JLog::add(__METHOD__ . '() will not catch phpmailerException objects as of 4.0.', JLog::WARNING, 'deprecated');

						return false;
					}
				}
			}
			else
			{
				$name = JMailHelper::cleanLine($name);

				foreach ($recipient as $to)
				{
					$to = JMailHelper::cleanLine($to);

					// Wrapped in try/catch if PHPMailer is configured to throw exceptions
					try
					{
						// Check for boolean false return if exception handling is disabled
						if (call_user_func('parent::' . $method, $to, $name) === false)
						{
							return false;
						}
					}
					catch (phpmailerException $e)
					{
						// The parent method will have already called the logging callback, just log our deprecated error handling message
						JLog::add(__METHOD__ . '() will not catch phpmailerException objects as of 4.0.', JLog::WARNING, 'deprecated');

						return false;
					}
				}
			}
		}
		else
		{
			$recipient = JMailHelper::cleanLine($recipient);

			// Wrapped in try/catch if PHPMailer is configured to throw exceptions
			try
			{
				// Check for boolean false return if exception handling is disabled
				if (call_user_func('parent::' . $method, $recipient, $name) === false)
				{
					return false;
				}
			}
			catch (phpmailerException $e)
			{
				// The parent method will have already called the logging callback, just log our deprecated error handling message
				JLog::add(__METHOD__ . '() will not catch phpmailerException objects as of 4.0.', JLog::WARNING, 'deprecated');

				return false;
			}
		}

		return $this;
	}

	/**
	 * Add recipients to the email
	 *
	 * @param   mixed  $recipient  Either a string or array of strings [email address(es)]
	 * @param   mixed  $name       Either a string or array of strings [name(s)]
	 *
	 * @return  JMail|boolean  Returns this object for chaining.
	 *
	 * @since   11.1
	 */
	public function addRecipient($recipient, $name = '')
	{
		return $this->add($recipient, $name, 'addAddress');
	}

	/**
	 * Add carbon copy recipients to the email
	 *
	 * @param   mixed  $cc    Either a string or array of strings [email address(es)]
	 * @param   mixed  $name  Either a string or array of strings [name(s)]
	 *
	 * @return  JMail|boolean  Returns this object for chaining on success or boolean false on failure.
	 *
	 * @since   11.1
	 */
	public function addCc($cc, $name = '')
	{
		// If the carbon copy recipient is an array, add each recipient... otherwise just add the one
		if (isset($cc))
		{
			return $this->add($cc, $name, 'addCC');
		}

		return $this;
	}

	/**
	 * Add blind carbon copy recipients to the email
	 *
	 * @param   mixed  $bcc   Either a string or array of strings [email address(es)]
	 * @param   mixed  $name  Either a string or array of strings [name(s)]
	 *
	 * @return  JMail|boolean  Returns this object for chaining on success or boolean false on failure.
	 *
	 * @since   11.1
	 */
	public function addBcc($bcc, $name = '')
	{
		// If the blind carbon copy recipient is an array, add each recipient... otherwise just add the one
		if (isset($bcc))
		{
			return $this->add($bcc, $name, 'addBCC');
		}

		return $this;
	}

	/**
	 * Add file attachment to the email
	 *
	 * @param   mixed   $path         Either a string or array of strings [filenames]
	 * @param   mixed   $name         Either a string or array of strings [names]
	 * @param   mixed   $encoding     The encoding of the attachment
	 * @param   mixed   $type         The mime type
	 * @param   string  $disposition  The disposition of the attachment
	 *
	 * @return  JMail|boolean  Returns this object for chaining on success or boolean false on failure.
	 *
	 * @since   12.2
	 * @throws  InvalidArgumentException
	 */
	public function addAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream', $disposition = 'attachment')
	{
		// If the file attachments is an array, add each file... otherwise just add the one
		if (isset($path))
		{
			// Wrapped in try/catch if PHPMailer is configured to throw exceptions
			try
			{
				$result = true;

				if (is_array($path))
				{
					if (!empty($name) && count($path) != count($name))
					{
						throw new InvalidArgumentException('The number of attachments must be equal with the number of name');
					}

					foreach ($path as $key => $file)
					{
						if (!empty($name))
						{
							$result = parent::addAttachment($file, $name[$key], $encoding, $type);
						}
						else
						{
							$result = parent::addAttachment($file, $name, $encoding, $type);
						}
					}
				}
				else
				{
					$result = parent::addAttachment($path, $name, $encoding, $type);
				}

				// Check for boolean false return if exception handling is disabled
				if ($result === false)
				{
					return false;
				}
			}
			catch (phpmailerException $e)
			{
				// The parent method will have already called the logging callback, just log our deprecated error handling message
				JLog::add(__METHOD__ . '() will not catch phpmailerException objects as of 4.0.', JLog::WARNING, 'deprecated');

				return false;
			}
		}

		return $this;
	}

	/**
	 * Unset all file attachments from the email
	 *
	 * @return  JMail  Returns this object for chaining.
	 *
	 * @since   12.2
	 */
	public function clearAttachments()
	{
		parent::clearAttachments();

		return $this;
	}

	/**
	 * Unset file attachments specified by array index.
	 *
	 * @param   integer  $index  The numerical index of the attachment to remove
	 *
	 * @return  JMail  Returns this object for chaining.
	 *
	 * @since   12.2
	 */
	public function removeAttachment($index = 0)
	{
		if (isset($this->attachment[$index]))
		{
			unset($this->attachment[$index]);
		}

		return $this;
	}

	/**
	 * Add Reply to email address(es) to the email
	 *
	 * @param   mixed  $replyto  Either a string or array of strings [email address(es)]
	 * @param   mixed  $name     Either a string or array of strings [name(s)]
	 *
	 * @return  JMail|boolean  Returns this object for chaining on success or boolean false on failure.
	 *
	 * @since   11.1
	 */
	public function addReplyTo($replyto, $name = '')
	{
		return $this->add($replyto, $name, 'addReplyTo');
	}

	/**
	 * Sets message type to HTML
	 *
	 * @param   boolean  $ishtml  Boolean true or false.
	 *
	 * @return  JMail  Returns this object for chaining.
	 *
	 * @since   12.3
	 */
	public function isHtml($ishtml = true)
	{
		parent::isHTML($ishtml);

		return $this;
	}

	/**
	 * Send messages using $Sendmail.
	 *
	 * This overrides the parent class to remove the restriction on the executable's name containing the word "sendmail"
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function isSendmail()
	{
		// Prefer the Joomla configured sendmail path and default to the configured PHP path otherwise
		$sendmail = JFactory::getConfig()->get('sendmail', ini_get('sendmail_path'));

		// And if we still don't have a path, then use the system default for Linux
		if (empty($sendmail))
		{
			$sendmail = '/usr/sbin/sendmail';
		}

		$this->Sendmail = $sendmail;
		$this->Mailer   = 'sendmail';
	}

	/**
	 * Use sendmail for sending the email
	 *
	 * @param   string  $sendmail  Path to sendmail [optional]
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function useSendmail($sendmail = null)
	{
		$this->Sendmail = $sendmail;

		if (!empty($this->Sendmail))
		{
			$this->isSendmail();

			return true;
		}
		else
		{
			$this->isMail();

			return false;
		}
	}

	/**
	 * Use SMTP for sending the email
	 *
	 * @param   string   $auth    SMTP Authentication [optional]
	 * @param   string   $host    SMTP Host [optional]
	 * @param   string   $user    SMTP Username [optional]
	 * @param   string   $pass    SMTP Password [optional]
	 * @param   string   $secure  Use secure methods
	 * @param   integer  $port    The SMTP port
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function useSmtp($auth = null, $host = null, $user = null, $pass = null, $secure = null, $port = 25)
	{
		$this->SMTPAuth = $auth;
		$this->Host = $host;
		$this->Username = $user;
		$this->Password = $pass;
		$this->Port = $port;

		if ($secure == 'ssl' || $secure == 'tls')
		{
			$this->SMTPSecure = $secure;
		}

		if (($this->SMTPAuth !== null && $this->Host !== null && $this->Username !== null && $this->Password !== null)
			|| ($this->SMTPAuth === null && $this->Host !== null))
		{
			$this->isSMTP();

			return true;
		}
		else
		{
			$this->isMail();

			return false;
		}
	}

	/**
	 * Function to send an email
	 *
	 * @param   string   $from         From email address
	 * @param   string   $fromName     From name
	 * @param   mixed    $recipient    Recipient email address(es)
	 * @param   string   $subject      email subject
	 * @param   string   $body         Message body
	 * @param   boolean  $mode         false = plain text, true = HTML
	 * @param   mixed    $cc           CC email address(es)
	 * @param   mixed    $bcc          BCC email address(es)
	 * @param   mixed    $attachment   Attachment file name(s)
	 * @param   mixed    $replyTo      Reply to email address(es)
	 * @param   mixed    $replyToName  Reply to name(s)
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function sendMail($from, $fromName, $recipient, $subject, $body, $mode = false, $cc = null, $bcc = null, $attachment = null,
		$replyTo = null, $replyToName = null)
	{
		// Create config object
		$config = JFactory::getConfig();

		$this->setSubject($subject);
		$this->setBody($body);

		// Are we sending the email as HTML?
		$this->isHtml($mode);

		/*
		 * Do not send the message if adding any of the below items fails
		 */

		if ($this->addRecipient($recipient) === false)
		{
			return false;
		}

		if ($this->addCc($cc) === false)
		{
			return false;
		}

		if ($this->addBcc($bcc) === false)
		{
			return false;
		}

		if ($this->addAttachment($attachment) === false)
		{
			return false;
		}

		// Take care of reply email addresses
		if (is_array($replyTo))
		{
			$numReplyTo = count($replyTo);

			for ($i = 0; $i < $numReplyTo; $i++)
			{
				if ($this->addReplyTo($replyTo[$i], $replyToName[$i]) === false)
				{
					return false;
				}
			}
		}
		elseif (isset($replyTo))
		{
			if ($this->addReplyTo($replyTo, $replyToName) === false)
			{
				return false;
			}
		}
		elseif ($config->get('replyto'))
		{
			$this->addReplyTo($config->get('replyto'), $config->get('replytoname'));
		}

		// Add sender to replyTo only if no replyTo received
		$autoReplyTo = (empty($this->ReplyTo)) ? true : false;

		if ($this->setSender(array($from, $fromName, $autoReplyTo)) === false)
		{
			return false;
		}

		return $this->Send();
	}

	/**
	 * Sends mail to administrator for approval of a user submission
	 *
	 * @param   string  $adminName   Name of administrator
	 * @param   string  $adminEmail  Email address of administrator
	 * @param   string  $email       [NOT USED TODO: Deprecate?]
	 * @param   string  $type        Type of item to approve
	 * @param   string  $title       Title of item to approve
	 * @param   string  $author      Author of item to approve
	 * @param   string  $url         A URL to included in the mail
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function sendAdminMail($adminName, $adminEmail, $email, $type, $title, $author, $url = null)
	{
		$subject = JText::sprintf('JLIB_MAIL_USER_SUBMITTED', $type);

		$message = sprintf(JText::_('JLIB_MAIL_MSG_ADMIN'), $adminName, $type, $title, $author, $url, $url, 'administrator', $type);
		$message .= JText::_('JLIB_MAIL_MSG') . "\n";

		if ($this->addRecipient($adminEmail) === false)
		{
			return false;
		}

		$this->setSubject($subject);
		$this->setBody($message);

		return $this->Send();
	}
}
