<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Factory Provider. Allows easier injection of dependencies into the controllers for one off tasks
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
*/
class JControllerFactoryCms
{
	/*
	 * Checks the session for a valid token. Exits if an invalid token
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function checkSession()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	}
}
