<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	 * Flag to indicate if contact failed.
	 */
	private $contactSuccessful = true;

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
	 * Handle a form validation domain event.
	 * 
	 * Up to three error/warning messages are queued to show to the user on redirect.
	 * User data is saved in the session so form fields may be preloaded.
	 * 
	 * @param   ContactEventFormvalidationerroroccurred  $event  A domain event object.
	 * 
	 * @return  void
	 * 
	 * @since  __DEPLOY_VERSION__
	 */
	public function handleFormValidationError(ContactEventFormvalidationerroroccurred $event)
	{
		$app = JFactory::getApplication();
		$errors = $event->errors;
		$this->contactSuccessful = false;

		// Push up to three validation messages out to the user.
		for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
		{
			if ($errors[$i] instanceof Exception)
			{
				$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
			}
			else
			{
				$app->enqueueMessage($errors[$i], 'warning');
			}
		}

		// Save the data in the session.
		$app->setUserState('com_contact.contact.data', $event->data);

		// Redirect back to the contact form.
		$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $event->contactId->id, false));
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		// Get the data from POST
		$contactId = new JValueContactid((int) $this->input->getInt('id'));
		$data = $this->input->post->get('jform', array(), 'array');

		// Register local event listeners.
		$dispatcher = \JEventDispatcher::getInstance();
		$dispatcher->register('onContactEventFormvalidationerroroccurred', array($this, 'handleFormValidationError'));

		// Get the service layer.
		$service = new JService;

		// Get contact parameters.
		$params = $service->handle(new ContactQueryParams($contactId));

		// Check for a valid session cookie.
		if ($params->get('validate_session', 0)
			&& JFactory::getSession()->getState() != 'active')
		{
			JError::raiseWarning(403, JText::_('COM_CONTACT_SESSION_INVALID'));

			// Save the data in the session.
			$app->setUserState('com_contact.contact.data', $data);

			// Redirect back to the contact form.
			$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $contactId->id, false));

			return false;
		}

		// Execute the command to process the contact request.
		$service->handle(new ContactCommandRequestcontact($contactId, $data));

		// If the contact request attempt failed, simply return.
		if (!$this->contactSuccessful)
		{
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_contact.contact.data', null);

		// Set the message.
		$msg = JText::_('COM_CONTACT_EMAIL_THANKS');

		// If set, use the redirect from the parameters.
		if ($params->get('redirect'))
		{
			$this->setRedirect($params->get('redirect'), $msg);

			return true;
		}

		// Otherwise redirect back to where we came from.
		$this->setRedirect(JRoute::_('index.php?option=com_contact&view=contact&id=' . $contactId->id, false), $msg);

		return true;
	}
}
