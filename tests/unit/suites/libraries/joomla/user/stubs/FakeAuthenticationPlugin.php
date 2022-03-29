<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  User
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A mock user authentication plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  User
 * @since       1.7.0
 */
class PlgAuthenticationFake
{
	/**
	 * @var    string
	 * @since  1.7.0
	 */
	public $name = 'fake';

	/**
	 * Test...
	 *
	 * @param   array                   $credentials  @todo
	 * @param   array                   $options      @todo
	 * @param   JAuthenicationResponse  &$response    @todo
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		if ($credentials['username'] == 'test' && $credentials['password'] == 'test')
		{
			$response->status = JAuthentication::STATUS_SUCCESS;
		}
	}
}
