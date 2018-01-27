<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use PHPMailer\PHPMailer\Exception as phpmailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * A mail message built using the PHPMailer package as the underlying transport
 *
 * @since  __DEPLOY_VERSION__
 */
class MailMessage implements MailMessageInterface
{
	/**
	 * A PHPMailer instance for the message.
	 *
	 * @var    PHPMailer
	 * @since  __DEPLOY_VERSION__
	 */
	private $mailer;

	/**
	 * Flag indicating that mail sending on the platform is enabled.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $sendingEnabled;

	/**
	 * Message constructor.
	 *
	 * @param   PHPMailer  $mailer          A PHPMailer instance for the message.
	 * @param   bool       $sendingEnabled  Flag indicating that mail sending on the platform is enabled.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(PHPMailer $mailer, bool $sendingEnabled = true)
	{
		$this->mailer         = $mailer;
		$this->sendingEnabled = $sendingEnabled;
	}

	/**
	 * Add an address to the message.
	 *
	 * @param   string  $email   The email address to add.
	 * @param   string  $name    The name for this user.
	 * @param   string  $method  The PHPMailer method to be called
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	protected function add(string $email, string $name = '', string $method = 'addAddress')
	{
		// Wrapped in try/catch if PHPMailer is configured to throw exceptions
		try
		{
			if ($this->mailer->$method(MailHelper::cleanLine($email), MailHelper::cleanLine($name)) === false)
			{
				throw new phpmailerException($this->mailer->ErrorInfo);
			}
		}
		catch (phpmailerException $e)
		{
			throw new Exception\InvalidAddressException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * Add a BCC address for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addBcc(string $email, string $name = '')
	{
		$this->add($email, $name, 'addBCC');
	}

	/**
	 * Add a CC address for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addCc(string $email, string $name = '')
	{
		$this->add($email, $name, 'addCC');
	}

	/**
	 * Add a Reply-To address for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addReplyTo(string $email, string $name = '')
	{
		$this->add($email, $name, 'addReplyTo');
	}

	/**
	 * Add a recipient for the message.
	 *
	 * @param   string  $email  The email address of the recipient.
	 * @param   string  $name   The name of the recipient.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function addRecipient(string $email, string $name = '')
	{
		$this->add($email, $name, 'addAddress');
	}

	/**
	 * Get the mail transport in use.
	 *
	 * @return  PHPMailer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMailer()
	{
		return $this->mailer;
	}

	/**
	 * Add the message to the system's job queue.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\MailExceptionInterface
	 */
	public function queue(): bool
	{
		// TODO - Implement a queue
		return false;
	}

	/**
	 * Send the message.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\MailExceptionInterface
	 */
	public function send(): bool
	{
		if (!$this->sendingEnabled)
		{
			throw new Exception\SendingDisabledException(Text::_('JLIB_MAIL_FUNCTION_OFFLINE'));
		}

		if ($this->mailer->Mailer == 'mail' && !function_exists('mail'))
		{
			throw new Exception\MailFunctionDisabledException(Text::_('JLIB_MAIL_FUNCTION_DISABLED'));
		}

		// Wrapped in try/catch if PHPMailer is configured to throw exceptions
		try
		{
			if ($this->mailer->send() === false)
			{
				throw new phpmailerException($this->mailer->ErrorInfo);
			}
		}
		catch (phpmailerException $e)
		{
			throw new Exception\SendingFailedException($e->getMessage(), $e->getCode(), $e);
		}

		return true;
	}

	/**
	 * Set the body of the message.
	 *
	 * @param   string  $content  Body of the email.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setBody(string $content)
	{
		$this->mailer->Body = MailHelper::cleanText($content);
	}

	/**
	 * Set the date of the message.
	 *
	 * @param   \DateTimeInterface  $date  The date for the email.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDate(\DateTimeInterface $date)
	{
		$this->mailer->MessageDate = $date->format('D, j M Y H:i:s O');
	}

	/**
	 * Set the recipient of a read receipt for the message.
	 *
	 * @param   string  $email  The email address of the recipient for the read receipt.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setReadReceiptRecipient(string $email)
	{
		$this->mailer->ConfirmReadingTo = MailHelper::cleanLine($email);
	}

	/**
	 * Set the sender of the message.
	 *
	 * @param   string  $email  The email address of the sender.
	 * @param   string  $name   The name of the sender.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidAddressException
	 */
	public function setSender(string $email, string $name = '')
	{
		// Wrapped in try/catch if PHPMailer is configured to throw exceptions
		try
		{
			if ($this->mailer->setFrom(MailHelper::cleanLine($email), MailHelper::cleanLine($name)) === false)
			{
				throw new phpmailerException($this->mailer->ErrorInfo);
			}
		}
		catch (phpmailerException $e)
		{
			throw new Exception\InvalidAddressException($e->getMessage(), $e->getCode(), $e);
		}
	}

	/**
	 * Set the subject of the message.
	 *
	 * @param   string  $subject  Subject of the email.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSubject(string $subject)
	{
		$this->mailer->Subject = MailHelper::cleanLine($subject);
	}
}
