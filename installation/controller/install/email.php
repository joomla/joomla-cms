<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller class to e-mail the configuration info for the Joomla Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Controller
 * @since       3.1
 */
class InstallationControllerInstallEmail extends JControllerBase
{
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

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the setup model.
		$model = new InstallationModelSetup;

		// Get the options from the session
		$options = $model->getOptions();

		$name    = $options['admin_user'];
		$email   = $options['admin_email'];
		$subject = JText::sprintf(JText::_('INSTL_EMAIL_SUBJECT'), $options['site_name']);

		// Prepare email body
		$body = array();
		$body[] = JText::sprintf(JText::_('INSTL_EMAIL_HEADING'), $options['site_name']);
		$body[] = '';
		$body[] = array(JText::_('INSTL_SITE_NAME_LABEL'), $options['site_name']);

		$body[] = $this->emailTitle(JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'));
		$body[] = array(JText::_('JEMAIL'), $options['admin_email']);
		$body[] = array(JText::_('JUSERNAME'), $options['admin_user']);

		if ($options['summary_email_passwords'])
		{
			$body[] = array(JText::_('INSTL_ADMIN_PASSWORD_LABEL'), $options['admin_password']);
		}

		$body[] = $this->emailTitle(JText::_('INSTL_DATABASE'));
		$body[] = array(JText::_('INSTL_DATABASE_TYPE_LABEL'), $options['db_type']);
		$body[] = array(JText::_('INSTL_DATABASE_HOST_LABEL'), $options['db_host']);
		$body[] = array(JText::_('INSTL_DATABASE_USER_LABEL'), $options['db_user']);

		if ($options['summary_email_passwords'])
		{
			$body[] = array(JText::_('INSTL_DATABASE_PASSWORD_LABEL'), $options['db_pass']);
		}

		$body[] = array(JText::_('INSTL_DATABASE_NAME_LABEL'), $options['db_name']);
		$body[] = array(JText::_('INSTL_DATABASE_PREFIX_LABEL'), $options['db_prefix']);

		if (isset($options['ftp_enable']) && $options['ftp_enable'])
		{
			$body[] = $this->emailTitle(JText::_('INSTL_FTP'));
			$body[] = array(JText::_('INSTL_FTP_USER_LABEL'), $options['ftp_user']);

			if ($options['summary_email_passwords'])
			{
				$body[] = array( JText::_('INSTL_FTP_PASSWORD_LABEL'), $options['ftp_pass']);
			}

			$body[] = array(JText::_('INSTL_FTP_HOST_LABEL'), $options['ftp_host']);
			$body[] = array(JText::_('INSTL_FTP_PORT_LABEL'), $options['ftp_port']);
		}

		$max = 0;

		foreach ($body as $line)
		{
			if (is_array($line))
			{
				$max = max(array($max, strlen($line['0'])));
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
		$mail->setSender(array($email, $name));
		$mail->setSubject($subject);
		$mail->setBody($body);

		$r = new stdClass;
		$r->view = 'install';

		try
		{
			$mail->Send();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('INSTL_EMAIL_NOT_SENT'), 'notice');
			$r->view = 'complete';
		}

		$app->sendJsonResponse($r);
	}

	/**
	 * Prepares a title line for the e-mail
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
