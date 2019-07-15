<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mailto\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mailto\Site\Helper\MailtoHelper;

/**
 * Mailer Component Controller.
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
	/**
	 * Show the form so that the user can send the link to someone.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function mailto()
	{
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

		/** @var \Joomla\Component\Mailto\Site\Model\MailtoModel $model */
		$model   = $this->getModel('mailto');
		$data    = $model->getData();

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \RuntimeException($model->getError());
		}

		if (!$model->validate($form, $data))
		{
			$errors = $model->getErrors();

			foreach ($errors as $error)
			{
				$errorMessage = $error;

				if ($error instanceof \Exception)
				{
					$errorMessage = $error->getMessage();
				}

				$this->app->enqueueMessage($errorMessage, 'error');
			}

			$this->mailto();

			return;
		}

		// An array of email headers we do not want to allow as input
		$headers = array (
			'Content-Type:',
			'MIME-Version:',
			'Content-Transfer-Encoding:',
			'bcc:',
			'cc:'
		);

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we find one, send an unauthorized header and die.
		 */
		foreach ($data as $key => $value)
		{
			foreach ($headers as $header)
			{
				if (is_string($value) && strpos($value, $header) !== false)
				{
					throw new \Exception('', 403);
				}
			}
		}

		/*
		 * Free up memory
		 */
		unset($headers, $fields);

		$siteName = $this->app->get('sitename');
		$link     = MailtoHelper::validateHash($this->input->post->get('link', '', 'post'));

		// Verify that this is a local link
		if (!$link || !Uri::isInternal($link))
		{
			// Non-local url...
			$this->app->enqueueMessage(Text::_('COM_MAILTO_EMAIL_NOT_SENT'));

			return $this->mailto();
		}

		$subject_default = Text::sprintf('COM_MAILTO_SENT_BY', $data['sender']);
		$subject         = $data['subject'] !== '' ? $data['subject'] : $subject_default;

		// Check for a valid to address
		$error = false;

		if (!$data['emailto'] || !MailHelper::isEmailAddress($data['emailto']))
		{
			$error = Text::sprintf('COM_MAILTO_EMAIL_INVALID', $data['emailto']);

			$this->app->enqueueMessage($error, 'warning');
		}

		// Check for a valid from address
		if (!$data['emailfrom'] || !MailHelper::isEmailAddress($data['emailfrom']))
		{
			$error = Text::sprintf('COM_MAILTO_EMAIL_INVALID', $data['emailfrom']);

			$this->app->enqueueMessage($error, 'warning');
		}

		if ($error)
		{
			return $this->mailto();
		}

		// Build the message to send
		$msg  = Text::_('COM_MAILTO_EMAIL_MSG');
		$body = sprintf($msg, $siteName, $data['sender'], $data['emailfrom'], $link);

		// Clean the email data
		$subject = MailHelper::cleanSubject($subject);
		$body    = MailHelper::cleanBody($body);

		// To send we need to use punycode.
		$data['emailfrom'] = PunycodeHelper::emailToPunycode($data['emailfrom']);
		$data['emailfrom'] = MailHelper::cleanAddress($data['emailfrom']);
		$data['emailto']   = PunycodeHelper::emailToPunycode($data['emailto']);

		// Try to send the email
		try
		{
			$return = Factory::getMailer()->sendMail($data['emailfrom'], $data['sender'], $data['emailto'], $subject, $body);
		}
		catch (\Exception $exception)
		{
			try
			{
				Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

				$return = false;
			}
			catch (\RuntimeException $exception)
			{
				Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

				$return = false;
			}
		}

		if ($return !== true)
		{
			$this->setMessage(Text::_('COM_MAILTO_EMAIL_NOT_SENT'), 'notice');

			$this->setRedirect('index.php', 'COM_MAILTO_EMAIL_NOT_SENT');

			return $this->mailto();
		}

		$this->input->set('view', 'sent');
		$this->display();
	}
}
