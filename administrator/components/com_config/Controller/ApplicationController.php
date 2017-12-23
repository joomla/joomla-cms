<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;

/**
 * Controller for global configuration
 *
 * @since  1.5
 */
class ApplicationController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Cancel operation.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function cancel()
	{
		$this->setRedirect(\JRoute::_('index.php?option=com_cpanel'));
	}

	/**
	 * Saves the form
	 *
	 * @return  mixed
	 *
	 * @since  4.0.0
	 */
	public function save()
	{
		// Check for request forgeries.
		if (!\JSession::checkToken())
		{
			$this->setRedirect('index.php', \JText::_('JINVALID_TOKEN'), 'error');
		}

		// Check if the user is authorized to do this.
		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			$this->setRedirect('index.php', \JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Set FTP credentials, if given.
		\JClientHelper::setCredentialsFromRequest('ftp');

		/** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
		$model = $this->getModel('Application', 'Administrator');

		$data  = $this->input->post->get('jform', array(), 'array');

		// Complete data array if needed
		$oldData = $model->getData();
		$data = array_replace($oldData, $data);

		// Get request type
		$saveFormat = \JFactory::getDocument()->getType();

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
		$this->app->setUserState('com_config.config.global.data', $data);

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to redirect back.
			 */

			// Redirect back to the edit screen.
			$this->setRedirect(\JRoute::_('index.php?option=com_config', false));
		}

		// Attempt to save the configuration.
		$data   = $return;
		$return = $model->save($data);

		// Save the validated data in the session.
		$this->app->setUserState('com_config.config.global.data', $data);

		// Check the return value.
		if ($return === false)
		{
			/*
			 * The save method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save failed, go back to the screen and display a notice.
			$this->app->redirect(\JRoute::_('index.php?option=com_config', false));
		}

		// Set the success message.
		$this->app->enqueueMessage(\JText::_('COM_CONFIG_SAVE_SUCCESS'), 'message');

		// Set the redirect based on the task.
		switch ($this->input->getCmd('task'))
		{
			case 'apply':
				$this->setRedirect(\JRoute::_('index.php?option=com_config', false));
				break;

			case 'save':
			default:
				$this->setRedirect(\JRoute::_('index.php', false));
				break;
		}
	}

	/**
	 * Method to remove root in global configuration.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function removeroot()
	{
		// Check for request forgeries.
		if (!\JSession::checkToken('get'))
		{
			$this->setRedirect('index.php', \JText::_('JINVALID_TOKEN'), 'error');
		}

		// Check if the user is authorized to do this.
		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			$this->setRedirect('index.php', \JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Initialise model.

		/** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
		$model = $this->getModel('Application', 'Administrator');

		// Attempt to save the configuration and remove root.
		try
		{
			$model->removeroot();
		}
		catch (\RuntimeException $e)
		{
			// Save failed, go back to the screen and display a notice.
			$this->setRedirect('index.php', \JText::_('JERROR_SAVE_FAILED', $e->getMessage()), 'error');
		}

		// Set the redirect based on the task.
		$this->setRedirect(\JRoute::_('index.php'), \JText::_('COM_CONFIG_SAVE_SUCCESS'));
	}

	/**
	 * Method to send the test mail.
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	public function sendtestmail()
	{
		// Send json mime type.
		$this->app->mimeType = 'application/json';
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		// Check if user token is valid.
		if (!\JSession::checkToken('get'))
		{
			$this->app->enqueueMessage(\JText::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse;
			$this->app->close();
		}

		// Check if the user is authorized to do this.
		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			$this->app->enqueueMessage(\JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			echo new JsonResponse;
			$this->app->close();
		}

		/** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
		$model = $this->getModel('Application', 'Administrator');

		echo new JsonResponse($model->sendTestMail());

		$this->app->close();
	}

	/**
	 * Method to GET permission value and give it to the model for storing in the database.
	 *
	 * @return  boolean  true on success, false when failed
	 *
	 * @since   3.5
	 */
	public function store()
	{
		// Send json mime type.
		$this->app->mimeType = 'application/json';
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		// Check if user token is valid.
		if (!\JSession::checkToken('get'))
		{
			$this->app->enqueueMessage(\JText::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse;
			$this->app->close();
		}

		/** @var \Joomla\Component\Config\Administrator\Model\Application $model */
		$model = $this->getModel('Application', 'Administrator');
		echo new JsonResponse($model->storePermissions());
		$this->app->close();
	}
}
