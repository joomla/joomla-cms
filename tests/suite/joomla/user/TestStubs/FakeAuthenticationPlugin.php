<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * A mock user authentication plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  User
 * @since       11.1
 */
class plgAuthenticationFake
{
	/**
	 * @var    string
	 * @since  11.1
	 */
	public $name = 'fake';

	/**
	 * @param   array                   $credentials
	 * @param   array                   $options
	 * @param   JAuthenicationResponse  $response
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		if ($credentials['username'] == 'test' && $credentials['password'] == 'test')
		{
			$response->status = JAuthentication::STATUS_SUCCESS;
		}
	}
}
