<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Mailer Component Controller.
 *
 * @since  1.5
 */
class MailtoController extends JControllerLegacy
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

		$app     = JFactory::getApplication();
		$model   = $this->getModel('mailto');
		$data    = $model->getData();

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		if (!$model->validate($form, $data))
		{
			$errors = $model->getErrors();

			foreach ($errors as $error)
			{
				$errorMessage = $error;

				if ($error instanceof Exception)
				{
					$errorMessage = $error->getMessage();
				}

				$app->enqueueMessage($errorMessage, 'error');
			}

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
					JError::raiseError(403, '');
				}
			}
		}

		/*
		 * Free up memory
		 */
		unset($headers, $fields);

		$siteName = $app->get('sitename');
		$link     = MailtoHelper::validateHash($this->input->post->get('link', '', 'post'));

		// Verify that this is a local link
		if (!$link || !JUri::isInternal($link))
		{
			// Non-local url...
			JError::raiseNotice(500, JText::_('COM_MAILTO_EMAIL_NOT_SENT'));

			return $this->mailto();
		}

		$subject_default = JText::sprintf('COM_MAILTO_SENT_BY', $data['sender']);
		$subject         = $data['subject'] !== '' ? $data['subject'] : $subject_default;

		// Check for a valid to address
		$error = false;

		if (!$data['emailto'] || !JMailHelper::isEmailAddress($data['emailto']))
		{
			$error = JText::sprintf('COM_MAILTO_EMAIL_INVALID', $data['emailto']);

			JError::raiseWarning(0, $error);
		}

		// Check for a valid from address
		if (!$data['emailfrom'] || !JMailHelper::isEmailAddress($data['emailfrom']))
		{
			$error = JText::sprintf('COM_MAILTO_EMAIL_INVALID', $data['emailfrom']);

			JError::raiseWarning(0, $error);
		}

		if ($error)
		{
			return $this->mailto();
		}

		// Build the message to send
		$msg  = JText::_('COM_MAILTO_EMAIL_MSG');
		$body = sprintf($msg, $siteName, $data['sender'], $data['emailfrom'], $link);

		// Clean the email data
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// To send we need to use punycode.
		$data['emailfrom'] = JStringPunycode::emailToPunycode($data['emailfrom']);
		$data['emailfrom'] = JMailHelper::cleanAddress($data['emailfrom']);
		$data['emailto']   = JStringPunycode::emailToPunycode($data['emailto']);

		// Send the email
		if (JFactory::getMailer()->sendMail($data['emailfrom'], $data['sender'], $data['emailto'], $subject, $body) !== true)
		{
			JError::raiseNotice(500, JText::_('COM_MAILTO_EMAIL_NOT_SENT'));

			return $this->mailto();
		}

		$this->input->set('view', 'sent');
		$this->display();
	}
}
