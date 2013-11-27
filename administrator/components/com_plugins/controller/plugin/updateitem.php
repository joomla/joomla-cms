<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Save Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       3.2
*/
class PluginsControllerPluginUpdateitem extends JControllerUpdate
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
	 * @return  mixed  Calls $app->redirect() for all cases except JSON
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		parent::execute();

		// Must load after serving service-requests
		$form  = $this->model->getForm();

		// Validate the posted data.
		$return = $this->model->validate($form, $this->data);

		// Check for validation errors.
		if ($return === false)
		{
			 // The validate method enqueued all messages for us, so we just need to redirect back.

			// Save the data in the session.
			$this->app->setUserState('com_plugins.plugin.data', $data);

			// Redirect back to the edit screen.
			$this->app->redirect(JRoute::_('index.php?option=com_plugins&controller=j.displayform.plugin&extension_id='. $this->data['extension_id'], false));
		}

		// Attempt to save the data.
		$data = $return;
		$return = $this->model->save($data);

		// Check the return value.
		if ($return === false)
		{
			/*
			 * The save method enqueued all messages for us, so we just need to redirect back.
			 */

			// Save the data in the session.
			$this->app->setUserState('com_plugins.plugin.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->redirect(JRoute::_('index.php?option=com_plugins&controller=j.display.plugin&extension_id='. $this->data['extension_id'], false));
		}

		// Set the success message.
		$this->app->enqueueMessage(JText::_('COM_PLUGINS_SAVE_SUCCESS'));

		$option = $this->input->getString('task');

		if (empty($option))
		{
			$option = $this->input->getString('controller');
		}

		$options = explode('.', $option);

		// Set the redirect based on the task.
		switch ($options[parent::CONTROLLER_OPTION])
		{
			case 'apply':
				$this->app->redirect(JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='. $this->data['extension_id'], false));
				break;

			case 'save':
			default:
				$this->app->redirect(JRoute::_('index.php?option=com_plugins&view=plugins', false));
				break;
		}
	}
}
