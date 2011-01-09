<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model' );

/**
 * Login Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @since		1.5
 */
class LoginModelLogin extends JModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$credentials = array(
			'username' => JRequest::getVar('username', '', 'method', 'username'),
			'password' => JRequest::getVar('passwd', '', 'post', 'string', JREQUEST_ALLOWRAW)
		);
		$this->setState('credentials', $credentials);

		// check for return URL from the request first
		if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
			$return = base64_decode($return);
			if (!JURI::isInternal($return)) {
				$return = '';
			}
		}

		// Set the return URL if empty.
		if (empty($return)) {
			$return = 'index.php';
		}

		$this->setState('return', $return);
	}
}