<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Messages\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

/**
 * Messages Component Message Model
 *
 * @since  1.6
 */
class ConfigController extends BaseController
{
	/**
	 * Method to save a record.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		\JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = \JFactory::getApplication();
		$model = $this->getModel('Config', 'MessagesModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);

			return false;
		}

		$data = $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the main list.
			$this->setRedirect(\JRoute::_('index.php?option=com_messages&view=messages', false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Redirect back to the main list.
			$this->setMessage(Text::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(\JRoute::_('index.php?option=com_messages&view=messages', false));

			return false;
		}

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_MESSAGES_CONFIG_SAVED'));
		$this->setRedirect(\JRoute::_('index.php?option=com_messages&view=messages', false));

		return true;
	}
}
