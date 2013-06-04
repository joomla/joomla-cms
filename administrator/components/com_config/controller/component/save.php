<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Save Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.1
*/
class ConfigControllerComponentSave extends JControllerBase
{
	/**
	 * Method to save global configuration.
	 *
	 * @return  bool	True on success, false on failure.
	 *
	 * @since   1.5
	 */
	public function execute()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		$app    = JFactory::getApplication();
		$model  = new ConfigModelComponent;
		$form   = $model->getForm();
		$data   = $this->input->get('jform', array(), 'array');
		$id     = $this->input->getInt('id');
		$option = $this->input->get('component');

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $option))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$returnUri = $this->input->post->get('return', null, 'base64');

		if (!empty($returnUri))
		{
			$redirect = '&return=' . urlencode($returnUri);
		}

		// Validate the posted data.
		$return = $model->validate($form, $data);

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Redirect back to the edit screen.
			$app->redirect(JRoute::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false));

			return false;
		}

		// Attempt to save the configuration.
		$data	= array(
				'params'	=> $return,
				'id'		=> $id,
				'option'	=> $option
		);
		$return = $model->save($data);

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());

			$app->redirect(JRoute::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false), $message, 'error');

			return false;
		}

		// Added for testing ---------------------------

		$task = $app->input->get('task');
		$array = explode(".", $task);

		// ------------------------------------

		// Set the redirect based on the task.
		switch ($array[1])
		{
			case 'apply':
				$message = JText::_('COM_CONFIG_SAVE_SUCCESS');

				$app->redirect(JRoute::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false), $message);
				break;

			case 'save':
			default:
				$redirect = 'index.php';

				if (!empty($returnUri))
				{
					$redirect = base64_decode($returnUri);
				}

				$app->redirect(JRoute::_($redirect, false));
				break;
		}

		return true;
	}
}
