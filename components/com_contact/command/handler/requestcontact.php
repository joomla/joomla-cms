<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\CommandHandlerBase;

/**
 * Command handler for Request Contact commands.
 * 
 * @since  __DEPLOY__
 */
final class ContactCommandHandlerRequestcontact extends CommandHandlerBase
{
	/**
	 * Command handler.
	 * 
	 * @param   ContactCommandRequestcontact  $command  A command.
	 * 
	 * @return  array of DomainEvent objects.
	 * 
	 * @throws  RuntimeException
	 */
	public function handle(ContactCommandRequestcontact $command)
	{
		$contactId = $command->contactId;
		$data = $command->data;

		// Contact plugins
		JPluginHelper::importPlugin('contact');
		$dispatcher = JEventDispatcher::getInstance();

		// Get the model.
		$model = JModelLegacy::getInstance('Contact', 'ContactModel');

		// Kludge, for CLI, otherwise parameters get overwritten.
		// Do a dummy getState to force populateState to run first.
		$model->getState('dummy');

		// Set the component parameters into the model.
		$model->setState('params', JComponentHelper::getParams('com_contact'));

		// Get the contact from the model.
		$contact = $model->getItem($contactId->id);

		// Get the contact form.
		$form = $model->getForm();

		// Check we have a form object.
		if (!$form)
		{
			throw new RuntimeException('Contact form cannot be loaded');
		}

		// Validate the data with the form.
		if ($model->validate($form, $data) === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Raise a validation error domain event.
			return $this->releaseEvents(
				new ContactEventFormvalidationerroroccurred($contactId, $data, $errors)
			);
		}

		// Validation succeeded, continue with custom handlers
		$results = $dispatcher->trigger('onValidateContact', array(&$contact, &$data));

		// Process any errors that were raised by the custom handlers.
		foreach ($results as $result)
		{
			if ($result instanceof Exception)
			{
				// Raise a validation error domain event.
				return $this->releaseEvents(
					new ContactEventFormvalidationerroroccurred($contactId, $data, array($result))
				);
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$dispatcher->trigger('onSubmitContact', array(&$contact, &$data));

		// Contact has been validated.  Publish the event so that notification messages may be sent.
		return $this->releaseEvents(
			new ContactEventContactvalidated($contactId, $data, $contact)
		);
	}
}
