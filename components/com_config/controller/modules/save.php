<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Save Controller for module editing
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigControllerModulesSave extends JControllerBase
{
	/**
	 * Method to save module editing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		// Check if the user is authorized to do this.
		$user = JFactory::getUser();

		if (!$user->authorise('module.edit.frontend', 'com_modules.module.' . $this->input->get('id'))
			&& !$user->authorise('module.edit.frontend', 'com_modules'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get sumitted module id
		$moduleId = '&id=' . $this->input->get('id');

		// Get returnUri
		$returnUri = $this->input->post->get('return', null, 'base64');
		$redirect = '';

		if (!empty($returnUri))
		{
			$redirect = '&return=' . $returnUri;
		}

		// Access backend com_modules to be done
		JLoader::register('ModulesControllerModule', JPATH_ADMINISTRATOR . '/components/com_modules/controllers/module.php');
		JLoader::register('ModulesModelModule', JPATH_ADMINISTRATOR . '/components/com_modules/models/module.php');

		$controllerClass = new ModulesControllerModule;

		// Get a document object
		$document = JFactory::getDocument();

		// Set backend required params
		$document->setType('json');

		// Execute backend controller
		$return = $controllerClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$data = $this->input->post->get('jform', array(), 'array');

			$this->app->setUserState('com_config.modules.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->enqueueMessage(JText::_('JERROR_SAVE_FAILED'));
			$this->app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.modules' . $moduleId . $redirect, false));
		}

		// Redirect back to com_config display
		$this->app->enqueueMessage(JText::_('COM_CONFIG_MODULES_SAVE_SUCCESS'));

		// Set the redirect based on the task.
		switch ($this->options[3])
		{
			case 'apply':
				$this->app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.modules' . $moduleId . $redirect, false));
				break;

			case 'save':
			default:

				if (!empty($returnUri))
				{
					$redirect = base64_decode(urldecode($returnUri));

					// Don't redirect to an external URL.
					if (!JUri::isInternal($redirect))
					{
						$redirect = JUri::base();
					}
				}
				else
				{
					$redirect = JUri::base();
				}

				$this->app->redirect($redirect);
				break;
		}
	}
}
