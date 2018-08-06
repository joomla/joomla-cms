<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail\PHPMailer;

defined('_JEXEC') or die;

use Joomla\CMS\Mail\MailerInterface;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Mail\MailMessageInterface;
use Joomla\Registry\Registry;
use PHPMailer\PHPMailer\Exception as phpmailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Mailer service to create mail messages using PHPMailer.
 *
 * @since  __DEPLOY_VERSION__
 */
class Mailer implements MailerInterface, LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * The system configuration.
	 *
	 * @var    Registry
	 * @since  __DEPLOY_VERSION__
	 */
	private $config;

	/**
	 * Mailer constructor.
	 *
	 * @param   Registry  $config  The system configuration.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Registry $config)
	{
		$this->config = $config;
	}

	/**
	 * Creates a new mail message.
	 *
	 * @return  MailMessageInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createMessage(): MailMessageInterface
	{
		return new MailMessage($this->createPHPMailerInstance(), (bool) $this->config->get('mailonline', true));
	}

	/**
	 * Process a debug message from the PHPMailer API.
	 *
	 * @param   string   $message  The message to log.
	 * @param   integer  $level    The current debug level.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function processMailerDebug($message, $level)
	{
		if ($this->logger)
		{
			$this->logger->debug($message, ['category' => 'mail', 'level' => $level]);
		}
	}

	/**
	 * Create a new PHPMailer instance.
	 *
	 * @return  PHPMailer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createPHPMailerInstance(): PHPMailer
	{
		$mailer = new PHPMailer;

		// Use our custom language source for translations
		$mailer->setLanguage('joomla', dirname(__DIR__) . '/language');

		// Configure a callback function to handle errors when $this->edebug() is called
		$mailer->Debugoutput = [$this, 'processMailerDebug'];

		// If debug mode is enabled then set SMTPDebug to the maximum level
		if (JDEBUG)
		{
			$mailer->SMTPDebug = 4;
		}

		// Don't disclose the PHPMailer version
		$mailer->XMailer = ' ';

		// Clean the email address
		$mailfrom = MailHelper::cleanLine($this->config->get('mailfrom'));

		// Set default sender without Reply-to if the mailfrom is a valid address
		if (MailHelper::isEmailAddress($mailfrom))
		{
			// Wrap in try/catch to catch phpmailerExceptions if it is throwing them
			try
			{
				// Check for a false return value if exception throwing is disabled
				if ($mailer->setFrom($mailfrom, MailHelper::cleanLine($this->config->get('fromname')), false) === false)
				{
					throw new phpmailerException($mailer->ErrorInfo);
				}
			}
			catch (phpmailerException $e)
			{
				if ($this->logger)
				{
					$this->logger->warning('Could not set default sender in PHPMailer object.', ['exception' => $e, 'category' => 'mail']);
				}
			}
		}

		// Default mailer is to use PHP's mail function
		switch ($this->config->get('mailer'))
		{
			case 'smtp':
				$secure = $this->config->get('smtpsecure');

				$mailer->SMTPAuth = $this->config->get('smtpauth') == 0 ? null : 1;
				$mailer->Host     = $this->config->get('smtphost', 'localhost');
				$mailer->Username = $this->config->get('smtpuser', '');
				$mailer->Password = $this->config->get('smtppass', '');
				$mailer->Port     = $this->config->get('smtpport', 25);

				if ($secure == 'ssl' || $secure == 'tls')
				{
					$mailer->SMTPSecure = $secure;
				}

				if (($mailer->SMTPAuth !== null && !empty($mailer->Host) && !empty($mailer->Username) && !empty($mailer->Password))
					|| ($mailer->SMTPAuth === null && !empty($mailer->Host)))
				{
					$mailer->isSMTP();
				}
				else
				{
					$mailer->isMail();
				}

				break;

			case 'sendmail':
				// Prefer the Joomla configured sendmail path and default to the configured PHP path otherwise
				$sendmail = $this->config->get('sendmail', ini_get('sendmail_path'));

				// And if we still don't have a path, then use the system default for Linux
				if (empty($sendmail))
				{
					$sendmail = '/usr/sbin/sendmail';
				}

				$mailer->Sendmail = $sendmail;
				$mailer->Mailer   = 'sendmail';

				break;

			default:
				$mailer->isMail();

				break;
		}

		return $mailer;
	}
}
