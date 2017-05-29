<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Save Controller for global configuration
 *
 * @since  3.2
 */
class ConfigControllerApplicationSave extends JControllerBase
{
	/**
	 * Method to save global configuration.
	 *
	 * @return  mixed  Calls $app->redirect() for all cases except JSON
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->getApplication()->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			$this->getApplication()->redirect('index.php');
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			$this->getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->getApplication()->redirect('index.php');
		}

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		$model = new ConfigModelApplication;
		$data  = $this->getInput()->post->get('jform', array(), 'array');

		// Complete data array if needed
		$oldData = $model->getData();
		$data = array_replace($oldData, $data);

		// Get request type
		$saveFormat = JFactory::getDocument()->getType();

		// Handle service requests
		if ($saveFormat == 'json')
		{
			return $model->save($data);
		}

		// Must load after serving service-requests
		$form = $model->getForm();

		// Validate the posted data.
		$return = $model->validate($form, $data);

		// Save the posted data in the session.
		$this->getApplication()->setUserState('com_config.config.global.data', $data);

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to redirect back.
			 */

			// Redirect back to the edit screen.
			$this->getApplication()->redirect(JRoute::_('index.php?option=com_config&controller=config.display.application', false));
		}

		// Attempt to save the configuration.
		$data   = $return;
		$return = $model->save($data);

		// Save the validated data in the session.
		$this->getApplication()->setUserState('com_config.config.global.data', $data);

		// Check the return value.
		if ($return === false)
		{
			/*
			 * The save method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save failed, go back to the screen and display a notice.
			$this->getApplication()->redirect(JRoute::_('index.php?option=com_config&controller=config.display.application', false));
		}

		// Set the success message.
		$this->getApplication()->enqueueMessage(JText::_('COM_CONFIG_SAVE_SUCCESS'), 'message');

		// Set the redirect based on the task.
		switch ($this->options[3])
		{
			case 'apply':
				$this->getApplication()->redirect(JRoute::_('index.php?option=com_config', false));
				break;

			case 'save':
			default:
				$this->getApplication()->redirect(JRoute::_('index.php', false));
				break;
		}
	}
}
