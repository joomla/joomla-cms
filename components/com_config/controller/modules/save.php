<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @return  bool	True on success.
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
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect('index.php');
		}

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get sumitted module id
		$moduleId = $this->input->get('id');

		// Access back-end com_modules to be done
		JLoader::register('ModulesControllerModule', JPATH_ADMINISTRATOR . '/components/com_modules/controllers/module.php');
		JLoader::register('ModulesModelModule', JPATH_ADMINISTRATOR . '/components/com_modules/models/module.php');

		$controllerClass = new ModulesControllerModule;

		// Get a document object
		$document = JFactory::getDocument();

		// Set back-end required params
		$document->setType('json');

		// Execute back-end controller
		$return = $controllerClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED');
			$this->app->redirect(JRoute::_('index.php?option=com_config&view=modules&controller=config.display.modules&id='.$moduleId, false), $message, 'error');
		}

		// Set the success message.
		$message = JText::_('COM_MODULES_SAVE_SUCCESS');

		// Redirect back to com_config display
		$this->app->redirect(JRoute::_('index.php?option=com_config&view=modules&controller=config.display.modules&id='.$moduleId, false), $message);
	}
}
