<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Request action controller class.
 *
 * @since  3.9.0
 */
class PrivacyControllerRequest extends JControllerLegacy
{
	/**
	 * Method to confirm the information request.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function confirm()
	{
		// Check the request token.
		$this->checkToken('post');

		/** @var PrivacyModelConfirm $model */
		$model = $this->getModel('Confirm', 'PrivacyModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		$return	= $model->confirmRequest($data);

		// Check for a hard error.
		if ($return instanceof Exception)
		{
			// Get the error message to display.
			if (JFactory::getApplication()->get('error_reporting'))
			{
				$message = $return->getMessage();
			}
			else
			{
				$message = JText::_('COM_PRIVACY_ERROR_CONFIRMING_REQUEST');
			}

			// Go back to the confirm form.
			$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=confirm', false), $message, 'error');

			return false;
		}
		elseif ($return === false)
		{
			// Confirm failed.
			// Go back to the confirm form.
			$message = JText::sprintf('COM_PRIVACY_ERROR_CONFIRMING_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=confirm', false), $message, 'notice');

			return false;
		}
		else
		{
			// Confirm succeeded.
			$this->setRedirect(JRoute::_(JUri::root()), JText::_('COM_PRIVACY_CONFIRM_REQUEST_SUCCEEDED'), 'info');

			return true;
		}
	}

	/**
	 * Method to submit an information request.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function submit()
	{
		// Check the request token.
		$this->checkToken('post');

		/** @var PrivacyModelRequest $model */
		$model = $this->getModel('Request', 'PrivacyModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		$return	= $model->createRequest($data);

		// Check for a hard error.
		if ($return instanceof Exception)
		{
			// Get the error message to display.
			if (JFactory::getApplication()->get('error_reporting'))
			{
				$message = $return->getMessage();
			}
			else
			{
				$message = JText::_('COM_PRIVACY_ERROR_CREATING_REQUEST');
			}

			// Go back to the confirm form.
			$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=request', false), $message, 'error');

			return false;
		}
		elseif ($return === false)
		{
			// Confirm failed.
			// Go back to the confirm form.
			$message = JText::sprintf('COM_PRIVACY_ERROR_CREATING_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=request', false), $message, 'notice');

			return false;
		}
		else
		{
			// Confirm succeeded.
			$this->setRedirect(JRoute::_(JUri::root()), JText::_('COM_PRIVACY_CREATE_REQUEST_SUCCEEDED'), 'info');

			return true;
		}
	}

	/**
	 * Method to extend the privacy consent.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function remind()
	{
		// Check the request token.
		$this->checkToken('post');

		/** @var PrivacyModelConfirm $model */
		$model = $this->getModel('Remind', 'PrivacyModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		$return	= $model->remindRequest($data);

		// Check for a hard error.
		if ($return instanceof Exception)
		{
			// Get the error message to display.
			if (JFactory::getApplication()->get('error_reporting'))
			{
				$message = $return->getMessage();
			}
			else
			{
				$message = JText::_('COM_PRIVACY_ERROR_REMIND_REQUEST');
			}

			// Go back to the confirm form.
			$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=remind', false), $message, 'error');

			return false;
		}
		elseif ($return === false)
		{
			// Confirm failed.
			// Go back to the confirm form.
			$message = JText::sprintf('COM_PRIVACY_ERROR_CONFIRMING_REMIND_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=remind', false), $message, 'notice');

			return false;
		}
		else
		{
			// Confirm succeeded.
			$this->setRedirect(JRoute::_(JUri::root()), JText::_('COM_PRIVACY_CONFIRM_REMIND_SUCCEEDED'), 'info');

			return true;
		}
	}
}
