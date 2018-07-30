<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mailto\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Mailto\Site\Helper\MailtoHelper;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Mailer Component Controller.
 *
 * @package     Joomla.Site
 * @subpackage  com_mailto
 * @since       1.5
 */
class DisplayController extends BaseController
{
	/**
	 * Show the form so that the user can send the link to someone.
	 *
	 * @return  void
	 *
	 * @since 1.5
	 */
	public function mailto()
	{
		$this->app->getSession()->set('com_mailto.formtime', time());
		$this->input->set('view', 'mailto');
		$this->display();
	}

	/**
	 * Send the message and display a notice
	 *
	 * @return  void
	 *
	 * @since  1.5
	 */
	public function send()
	{
		// Check for request forgeries
		$this->checkToken();

		$session = $this->app->getSession();
		$timeout = $session->get('com_mailto.formtime', 0);

		if ($timeout == 0 || time() - $timeout < 20)
		{
			$this->setMessage(Text::_('COM_MAILTO_EMAIL_NOT_SENT'), 'notice');

			return $this->mailto();
		}

		$SiteName = $this->app->get('sitename');
		$link     = MailtoHelper::validateHash($this->input->get('link', '', 'post'));

		// Verify that this is a local link
		if (!$link || !Uri::isInternal($link))
		{
			// Non-local url...
			$this->setMessage(Text::_('COM_MAILTO_EMAIL_NOT_SENT'), 'notice');

			return $this->mailto();
		}

		// An array of email headers we do not want to allow as input
		$headers = array (
			'Content-Type:',
			'MIME-Version:',
			'Content-Transfer-Encoding:',
			'bcc:',
			'cc:'
		);

		// An array of the input fields to scan for injected headers
		$fields = array(
			'mailto',
			'sender',
			'from',
			'subject',
		);

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we find one, send an unauthorized header and die.
		 */
		foreach ($fields as $field)
		{
			foreach ($headers as $header)
			{
				if (strpos($this->input->post->getString($field), $header) !== false)
				{
					throw new \Exception('', 403);
				}
			}
		}

		/*
		 * Free up memory
		 */
		unset($headers, $fields);

		$email           = $this->input->post->getString('mailto', '');
		$sender          = $this->input->post->getString('sender', '');
		$from            = $this->input->post->getString('from', '');
		$subject_default = Text::sprintf('COM_MAILTO_SENT_BY', $sender);
		$subject         = $this->input->post->getString('subject', $subject_default);

		// Check for a valid to address
		$error = false;

		if (!$email || !MailHelper::isEmailAddress($email))
		{
			$error = Text::sprintf('COM_MAILTO_EMAIL_INVALID', $email);
			$this->app->enqueueMessage($error, 'warning');
		}

		// Check for a valid from address
		if (!$from || !MailHelper::isEmailAddress($from))
		{
			$error = Text::sprintf('COM_MAILTO_EMAIL_INVALID', $from);
			$this->app->enqueueMessage($error, 'warning');
		}

		if ($error)
		{
			return $this->mailto();
		}

		// Build the message to send
		$msg  = Text::_('COM_MAILTO_EMAIL_MSG');
		$body = sprintf($msg, $SiteName, $sender, $from, $link);

		// Clean the email data
		$subject = MailHelper::cleanSubject($subject);
		$body    = MailHelper::cleanBody($body);

		// To send we need to use punycode.
		$from  = PunycodeHelper::emailToPunycode($from);
		$from  = MailHelper::cleanAddress($from);
		$email = PunycodeHelper::emailToPunycode($email);

		// Send the email
		if (Factory::getMailer()->sendMail($from, $sender, $email, $subject, $body) !== true)
		{
			$this->setMessage(Text::_('COM_MAILTO_EMAIL_NOT_SENT'), 'notice');

			return $this->mailto();
		}

		$this->input->set('view', 'sent');
		$this->display();
	}
}
