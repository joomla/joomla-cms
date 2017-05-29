<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller;

/**
 * Component Controller
 *
 * @since  1.5
 */
class Config extends Controller
{
	/**
	 * Method to handle cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function cancel()
	{
		// Redirect back to home(base) page
		$this->setRedirect(\JUri::base());
	}

	/**
	 * Method to save global configuration.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function save()
	{
		// Check for request forgeries.
		if (!\JSession::checkToken())
		{
			$this->app->enqueueMessage(\JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		// Check if the user is authorized to do this.
		if (!\JFactory::getUser()->authorise('core.admin'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		// Set FTP credentials, if given.
		\JClientHelper::setCredentialsFromRequest('ftp');

		$model = new \Joomla\Component\Config\Site\Model\Config;
		$form  = $model->getForm();
		$data  = $this->app->input->post->get('jform', array(), 'array');

		// Validate the posted data.
		$return = $model->validate($form, $data);

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save the data in the session.
			$this->app->setUserState('com_config.config.global.data', $data);

			// Redirect back to the edit screen.
			$this->app->redirect(\JRoute::_('index.php?option=com_config&view=config', false));
		}

		// Attempt to save the configuration.
		$data = $return;

		// Access backend com_config
		\JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config');
		$saveClass = new \Joomla\Component\Messages\Administrator\Controller\Config;

		// Get a document object
		$document = \JFactory::getDocument();

		// Set backend required params
		$document->setType('json');

		// Execute backend controller
		$return = $saveClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			/*
			 * The save method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save the data in the session.
			$this->app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->redirect(\JRoute::_('index.php?option=com_config&controller=config.display.config', false));
		}

		// Redirect back to com_config display
		$this->app->enqueueMessage(\JText::_('COM_CONFIG_SAVE_SUCCESS'));
		$this->app->redirect(\JRoute::_('index.php?option=com_config&controller=config.display.config', false));

		return true;
	}

	/**
	 * Method to display global configuration.
	 *
	 * @return  boolean	True on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function display($cachable = false, $urlparams = array())
	{
		die('wtf');

		// Get the application
		$app = $this->getApplication();

		// Get the document object.
		$document     = JFactory::getDocument();

		$viewName     = $this->getInput()->getWord('view', 'config');
		$viewFormat   = $document->getType();
		$layoutName   = $this->getInput()->getWord('layout', 'default');

		// Access backend com_config
		JLoader::registerPrefix(ucfirst($viewName), JPATH_ADMINISTRATOR . '/components/com_config');
		$displayClass = new ConfigControllerApplicationDisplay;

		// Set backend required params
		$document->setType('json');
		$app->input->set('view', 'application');

		// Execute backend controller
		$serviceData = json_decode($displayClass->execute(), true);

		// Reset params back after requesting from service
		$document->setType('html');
		$app->input->set('view', $viewName);

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/view/' . $viewName . '/tmpl', 'normal');

		$viewClass  = 'ConfigView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'ConfigModel' . ucfirst($viewName);

		if (class_exists($viewClass))
		{
			if ($viewName !== 'close')
			{
				$model = new $modelClass;

				// Access check.
				if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option')))
				{
					return;
				}
			}

			$view = new $viewClass($model, $paths);

			$view->setLayout($layoutName);

			// Push document object into the view.
			$view->document = $document;

			// Load form and bind data
			$form = $model->getForm();

			if ($form)
			{
				$form->bind($serviceData);
			}

			// Set form and data to the view
			$view->form = &$form;
			$view->data = &$serviceData;
		}

		$view->display();
	}
}
