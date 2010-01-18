<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Reset controller class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersControllerReset extends UsersController
{
	/**
	 * Method to request a password reset.
	 *
	 * @access	public
	 * @since	1.0
	 */
	function request()
	{
		// Check the request token.
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('User', 'UsersModel');
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Submit the password reset request.
		$return	= $model->processResetRequest($data);

		// Check for a hard error.
		if (JError::isError($return))
		{
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('USERS_RESET_REQUEST_ERROR');
			}

			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset'.$itemid;

			// Go back to the request form.
			$this->setRedirect(JRoute::_($route, false), $message, 'error');
			return false;
		}
		// The request failed.
		elseif ($return === false)
		{
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset'.$itemid;

			// Go back to the request form.
			$message = JText::sprintf('USERS_RESET_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');
			return false;
		}
		// The request succeeded.
		else
		{
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;

			// Proceed to step two.
			$message = JText::_('USERS_RESET_REQUEST_SUCCESS');
			$this->setRedirect(JRoute::_($route, false), $message);
			return true;
		}
	}

	/**
	 * Method to confirm the password request.
	 *
	 * @access	public
	 * @since	1.0
	 */
	function confirm()
	{
		// Check the request token.
		JRequest::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('User', 'UsersModel');
		$data	= JRequest::getVar('jform', array(), 'request', 'array');

		// Confirm the password reset request.
		$return	= $model->processResetConfirm($data);

		// Check for a hard error.
		if (JError::isError($return))
		{
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('USERS_RESET_CONFIRM_ERROR');
			}

			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;

			// Go back to the confirm form.
			$this->setRedirect(JRoute::_($route, false), $message, 'error');
			return false;
		}
		// Confirm failed.
		elseif ($return === false)
		{
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;

			// Go back to the confirm form.
			$message = JText::sprintf('USERS_RESET_CONFIRM_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');
			return false;
		}
		// Confirm succeeded.
		else
		{
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset&layout=complete'.$itemid;

			// Proceed to step three.
			$this->setRedirect(JRoute::_($route, false));
			return true;
		}
	}

	/**
	 * Method to complete the password reset process.
	 *
	 * @access	public
	 * @since	1.0
	 */
	function complete()
	{
		// Check for request forgeries
		JRequest::checkToken('post') or jexit(JText::_('JInvalid_Token'));

		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('User', 'UsersModel');
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Complete the password reset request.
		$return	= $model->processResetComplete($data);

		// Check for a hard error.
		if (JError::isError($return))
		{
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('USERS_RESET_COMPLETE_ERROR');
			}

			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset&layout=complete'.$itemid;

			// Go back to the complete form.
			$this->setRedirect(JRoute::_($route, false), $message, 'error');
			return false;
		}
		// Complete failed.
		elseif ($return === false)
		{
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getResetRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=reset&layout=complete'.$itemid;

			// Go back to the complete form.
			$message = JText::sprintf('USERS_RESET_COMPLETE_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');
			return false;
		}
		// Complete succeeded.
		else
		{
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getLoginRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=login'.$itemid;

			// Proceed to the login form.
			$message = JText::_('USERS_RESET_COMPLETE_SUCCESS');
			$this->setRedirect(JRoute::_($route, false), $message);
			return true;
		}
	}
}