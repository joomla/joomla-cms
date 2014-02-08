<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Remove Root Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigControllerApplicationRemoveroot extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Method to remove root in global configuration.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken('get'))
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

		// Initialise model.
		$model = new ConfigModelApplication;

		// Attempt to save the configuration and remove root.
		try
		{
			$model->removeroot();
		}
		catch (RuntimeException $e)
		{
			// Save failed, go back to the screen and display a notice.
			$this->app->enqueueMessage(JText::sprintf('JERROR_SAVE_FAILED', $e->getMessage()), 'error');
			$this->app->redirect(JRoute::_('index.php', false));
		}

		// Set the redirect based on the task.
		$this->app->enqueueMessage(JText::_('COM_CONFIG_SAVE_SUCCESS'));
		$this->app->redirect(JRoute::_('index.php', false));
	}
}
