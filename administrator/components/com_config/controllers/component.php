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
 * Note: this view is intended only to be opened in a popup
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigControllerComponent extends JControllerLegacy
{
	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Cancel operation
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	function cancel()
	{
		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_config.config.global.data', null);

		$return = $this->input->post->get('return', null, 'base64');
		$redirect = 'index.php';
		if (!empty($return))
		{
			$redirect = base64_decode($return);
		}

		$this->setRedirect($redirect);
	}

	/**
	 * Save the configuration
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');

		$app = JFactory::getApplication();
		$model = $this->getModel('Component');
		$form = $model->getForm();
		$data = $this->input->get('jform', array(), 'array');
		$id = $this->input->getInt('id');
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
			$errors = $model->getErrors();

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
			$this->setRedirect(JRoute::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false));
			return false;
		}

		// Attempt to save the configuration.
		$data = array(
			'params' => $return,
			'id' => $id,
			'option' => $option
		);
		$return = $model->save($data);

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_config&view=component&component=' . $option . $redirect, $message, 'error');
			return false;
		}

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('COM_CONFIG_SAVE_SUCCESS');

				$this->setRedirect('index.php?option=com_config&view=component&component=' . $option . $redirect, $message);
				break;

			case 'save':
			default:
				$redirect = 'index.php';
				if (!empty($returnUri))
				{
					$redirect = base64_decode($returnUri);
				}

				$this->setRedirect($redirect);
				break;
		}

		return true;
	}
}
