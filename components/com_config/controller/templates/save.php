<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Save Controller for global configuration
 *
 * @since  3.2
 */
class ConfigControllerTemplatesSave extends JControllerBase
{
	/**
	 * Method to save global configuration.
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
			JFactory::getApplication()->redirect('index.php', JText::_('JINVALID_TOKEN'));
		}

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		$app = JFactory::getApplication();

		// Access backend com_templates
		JLoader::register('TemplatesControllerStyle', JPATH_ADMINISTRATOR . '/components/com_templates/controllers/style.php');
		JLoader::register('TemplatesModelStyle', JPATH_ADMINISTRATOR . '/components/com_templates/models/style.php');
		JLoader::register('TemplatesTableStyle', JPATH_ADMINISTRATOR . '/components/com_templates/tables/style.php');
		$controllerClass = new TemplatesControllerStyle;

		// Get a document object
		$document = JFactory::getDocument();

		// Set backend required params
		$document->setType('json');
		$this->input->set('id', $app->getTemplate(true)->id);

		// Execute backend controller
		$return = $controllerClass->save();

		// Reset params back after requesting from service
		$document->setType('html');

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED');

			$app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.templates', false), $message, 'error');

			return false;
		}

		// Set the success message.
		$message = JText::_('COM_CONFIG_SAVE_SUCCESS');

		// Redirect back to com_config display
		$app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.templates', false), $message);

		return true;
	}
}
