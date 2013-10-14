<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/
class ConfigControllerUpdate extends JControllerCmsbase
{

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Check for request forgeries.
		if(!JSession::checkToken())
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JINVALID_TOKEN'));
		}

		$viewName     = $this->input->getWord('view');
		$viewFormat   = $document->getType();
		// Use for apply
		$layoutName   = $this->input->getWord('layout', 'edit');

		$model = new $this->prefix . 'Model' . $viewName ;
		$data  = $this->input->post->get('jform', array(), 'array');

		// Complete data array if needed
		$oldData = $model->getData();
		$data = array_replace($oldData, $data);

		// Get request type
		$saveFormat   = JFactory::getDocument()->getType();

		// Handle service requests
		if ($saveFormat == 'json')
		{
			return $model->save($data);
		}

		// Must load after serving service-requests
		$form  = $model->getForm();

		// Validate the posted data.
		$return = $model->validate($form, $data);

		return $return;

	}

	public function validateFormData()
	{
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
			//@todo fix this location
			//$app->setUserState('com_config.config.global.data', $data);

			// Redirect back to the edit screen.
			$app->redirect(JRoute::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false));

			return false;

		}

		return true;
	}

}