<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller for single contact view
 *
 * @since  1.5.19
 */
class ContactControllerContact extends JControllerForm
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6.4
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Method to submit the contact form and send an email.
	 *
	 * @return  boolean  True on success sending the email. False on failure.
	 *
	 * @since   1.5.19
	 */
	public function submit()
	{
		// Check for request forgeries.
		$this->checkToken();

		$app    = JFactory::getApplication();
		$model  = $this->getModel('contact');
		$stub   = $this->input->getString('id');
		$id     = (int) $stub;

		// Get the data from POST
		$data = $this->input->post->get('jform', array(), 'array');

		// Get item
		$model->setState('filter.published', 1);
		$contact = $model->getItem($id);

		// Get item params, take menu parameters into account if necessary
		$active = $app->getMenu()->getActive();
		$stateParams = clone $model->getState()->get('params');

		// If the current view is the active item and a contact view for this contact, then the menu item params take priority
		if ($active && strpos($active->link, 'view=contact') && strpos($active->link, '&id=' . (int) $contact->id))
		{
			// $item->params are the contact params, $temp are the menu item params
			// Merge so that the menu item params take priority
			$contact->params->merge($stateParams);
		}
		else
		{
			// Current view is not a single contact, so the contact params take priority here
			$stateParams->merge($contact->params);
			$contact->params = $stateParams;
		}

		// Check if the contact form is enabled
		if (!$contact->params->get('show_email_form'))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false));

			return false;
		}

		// Check for a valid session cookie
		if ($contact->params->get('validate_session', 0))
		{
			if (JFactory::getSession()->getState() !== 'active')
			{
				JError::raiseWarning(403, JText::_('JLIB_ENVIRONMENT_SESSION_INVALID'));

				// Save the data in the session.
				$app->setUserState('com_contact.contact.data', $data);

				// Redirect back to the contact form.
				$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false));

				return false;
			}
		}

		// Contact plugins
		JPluginHelper::importPlugin('contact');
		$dispatcher = JEventDispatcher::getInstance();

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

			$app->setUserState('com_contact.contact.data', $data);

			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false));

			return false;
		}

		// Validation succeeded, continue with custom handlers
		$results = $dispatcher->trigger('onValidateContact', array(&$contact, &$data));

		foreach ($results as $result)
		{
			if ($result instanceof Exception)
			{
				return false;
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$dispatcher->trigger('onSubmitContact', array(&$contact, &$data));

		// Send the email
		$sent = false;

		if (!$contact->params->get('custom_reply'))
		{
			$sent = $this->_sendEmail($data, $contact, $contact->params->get('show_email_copy', 0));
		}

		// Set the success message if it was a success
		if (!($sent instanceof Exception))
		{
			$msg = JText::_('COM_CONTACT_EMAIL_THANKS');
		}
		else
		{
			$msg = '';
		}

		// Flush the data from the session
		$app->setUserState('com_contact.contact.data', null);

		// Redirect if it is set in the parameters, otherwise redirect back to where we came from
		if ($contact->params->get('redirect'))
		{
			$this->setRedirect($contact->params->get('redirect'), $msg);
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $stub . '&catid=' . $contact->catid, false), $msg);
		}

		return true;
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
	private function _sendEmail($data, $contact, $copy_email_activated)
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

		// Load the custom fields
		if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields']))
		{
			$output = FieldsHelper::render(
				'com_contact.mail',
				'fields.render',
				array(
					'context' => 'com_contact.mail',
					'item'    => $contact,
					'fields'  => $fields,
				)
			);

			if ($output)
			{
				$body .= "\r\n\r\n" . $output;
			}
		}

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
			$mail->addReplyTo($email, $name);
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		return $sent;
	}
}
