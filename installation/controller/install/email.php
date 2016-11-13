<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use PHPMailer\PHPMailer\Exception as phpmailerException;

/**
 * Controller class to email the configuration info for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerInstallEmail extends JControllerBase
{
	/**
	 * Constructor.
	 *
	 * @since   3.2
	 */
	public function __construct()
	{
		parent::__construct();

		/** @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Overrides application config so the send function will work
		$app->loadConfiguration(new JConfig);
	}

	/**
	 * Execute the controller.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function execute()
	{
		// Get the application
		/* @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Check for request forgeries. - @TODO - Restore this check
		// JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the options from the session
		$options = (new InstallationModelSetup)->getOptions();

		$name    = $options['admin_user'];
		$email   = $options['admin_email'];
		$subject = JText::sprintf(JText::_('INSTL_EMAIL_SUBJECT'), $options['site_name']);

		// Prepare email body
		$body   = [];
		$body[] = JText::sprintf(JText::_('INSTL_EMAIL_HEADING'), $options['site_name']);
		$body[] = '';
		$body[] = [JText::_('INSTL_SITE_NAME_LABEL'), $options['site_name']];

		$body[] = $this->emailTitle(JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'));
		$body[] = [JText::_('JEMAIL'), $options['admin_email']];
		$body[] = [JText::_('JUSERNAME'), $options['admin_user']];

		if ($options['summary_email_passwords'])
		{
			$body[] = [JText::_('INSTL_ADMIN_PASSWORD_LABEL'), $options['admin_password']];
		}

		$body[] = $this->emailTitle(JText::_('INSTL_DATABASE'));
		$body[] = [JText::_('INSTL_DATABASE_TYPE_LABEL'), $options['db_type']];
		$body[] = [JText::_('INSTL_DATABASE_HOST_LABEL'), $options['db_host']];
		$body[] = [JText::_('INSTL_DATABASE_USER_LABEL'), $options['db_user']];

		if ($options['summary_email_passwords'])
		{
			$body[] = [JText::_('INSTL_DATABASE_PASSWORD_LABEL'), $options['db_pass']];
		}

		$body[] = [JText::_('INSTL_DATABASE_NAME_LABEL'), $options['db_name']];
		$body[] = [JText::_('INSTL_DATABASE_PREFIX_LABEL'), $options['db_prefix']];

		if (isset($options['ftp_enable']) && $options['ftp_enable'])
		{
			$body[] = $this->emailTitle(JText::_('INSTL_FTP'));
			$body[] = [JText::_('INSTL_FTP_USER_LABEL'), $options['ftp_user']];

			if ($options['summary_email_passwords'])
			{
				$body[] = [JText::_('INSTL_FTP_PASSWORD_LABEL'), $options['ftp_pass']];
			}

			$body[] = [JText::_('INSTL_FTP_HOST_LABEL'), $options['ftp_host']];
			$body[] = [JText::_('INSTL_FTP_PORT_LABEL'), $options['ftp_port']];
		}

		$max = 0;

		foreach ($body as $line)
		{
			if (is_array($line))
			{
				$max = max([$max, strlen($line['0'])]);
			}
		}

		foreach ($body as $i => $line)
		{
			if (is_array($line))
			{
				$label = $line['0'];
				$label .= ': ' . str_repeat(' ', $max - strlen($label));
				$body[$i] = $label . $line['1'];
			}
		}

		$body = implode("\r\n", $body);

		$mail = JFactory::getMailer();
		$mail->addRecipient($email);
		$mail->addReplyTo($email, $name);
		$mail->setSender([$email, $name]);
		$mail->setSubject($subject);
		$mail->setBody($body);

		$r       = new stdClass;
		$r->view = 'complete';

		try
		{
			if (!$mail->Send())
			{
				$app->enqueueMessage(JText::_('INSTL_EMAIL_NOT_SENT'), 'error');
			}
		}
		catch (phpmailerException $e)
		{
			$app->enqueueMessage(JText::_('INSTL_EMAIL_NOT_SENT'), 'error');
		}

		$app->sendJsonResponse($r);
	}

	/**
	 * Prepares a title line for the email
	 *
	 * @param   string  $title  The title pre-formatting
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function emailTitle($title)
	{
		return "\r\n" . $title . "\r\n" . str_repeat('=', strlen($title));
	}
}
