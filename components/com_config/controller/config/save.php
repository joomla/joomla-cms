<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Save Controller for global configuration
 *
 * @since  3.2
 */
class ConfigControllerConfigSave extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

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
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN_NOTICE'));
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

		$model = new ConfigModelConfig;
		$form  = $model->getForm();
		$data  = $this->input->post->get('jform', array(), 'array');

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
			$this->app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.config', false));
		}

		// Attempt to save the configuration.
		$data = $return;

		// Access backend com_config
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config');
		$saveClass = new ConfigControllerApplicationSave;

		// Get a document object
		$document = JFactory::getDocument();

		// Set backend required params
		$document->setType('json');

		// Execute backend controller
		$return = $saveClass->execute();

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
			$this->app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.config', false));
		}

		// Redirect back to com_config display
		$this->app->enqueueMessage(JText::_('COM_CONFIG_SAVE_SUCCESS'));
		$this->app->redirect(JRoute::_('index.php?option=com_config&controller=config.display.config', false));

		return true;
	}
}
