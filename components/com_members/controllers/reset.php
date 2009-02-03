<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

/**
 * Reset controller class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @since		1.6
 */
class MembersControllerReset extends MembersController
{
	/**
	 * Method to request a password reset.
	 */
	public function request()
	{
		// Check the request token.
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Reset', 'MembersModel');
		$data	= JRequest::getVar('default', array(), 'post', 'array');

		// Submit the password reset request.
		$return	= $model->request($data);

		// Check for an error.
		if (JError::isError($return))
		{
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('MEMBERS RESET REQUEST ERROR');
			}

			// Go back to the request form.
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset', false), $message, 'error');
		}
		// The request failed.
		elseif ($return === false)
		{
			// Go back to the request form.
			$message = JText::_('MEMBERS RESET REQUEST FAILED');
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset', false), $message, 'notice');
		}
		// The request succeeded.
		else
		{
			// Proceed to step two.
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset&layout=confirm', false));
		}
	}

	/**
	 * Method to confirm the password request.
	 */
	public function confirm()
	{
		// Check the request token.
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Reset', 'MembersModel');
		$data	= JRequest::getVar('default', array(), 'post', 'array');

		// Confirm the password reset request.
		$return	= $model->confirm($data);

		// Check for an error.
		if (JError::isError($return))
		{
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('MEMBERS RESET CONFIRM ERROR');
			}

			// Go back to the confirm form.
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset&layout=confirm', false), $message, 'error');
		}
		// Confirm failed.
		elseif ($return === false)
		{
			// Go back to the confirm form.
			$message = JText::_('MEMBERS RESET CONFIRM FAILED');
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset&layout=confirm', false), $message, 'notice');
		}
		// Confirm succeeded.
		else
		{
			// Proceed to step three.
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset&layout=complete', false));
		}
	}

	/**
	 * Method to complete the password reset process.
	 */
	public function complete()
	{
		// Check the request token.
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Reset', 'MembersModel');
		$data	= JRequest::getVar('default', array(), 'post', 'array');

		// Complete the password reset request.
		$return	= $model->complete($data);

		// Check for an error.
		if (JError::isError($return))
		{
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('MEMBERS RESET COMPLETE ERROR');
			}

			// Go back to the confirm form.
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset&layout=complete', false), $message, 'error');
		}
		// Complete failed.
		elseif ($return === false)
		{
			// Go back to the confirm form.
			$message = JText::_('MEMBERS RESET COMPLETE FAILED');
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=reset&layout=complete', false), $message, 'notice');
		}
		// Complete succeeded.
		else
		{
			// Proceed to the login form..
			$this->setRedirect(JRoute::_('index.php?option=com_members&view=login', false));
		}
	}
}