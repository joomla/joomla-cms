<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Authentication;

defined('_JEXEC') or die;

/**
 * Interface for authentication plugins
 *
 * @since  __DEPLOY_VERSION__
 */
interface AuthenticationPluginInterface
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array                   $credentials  Array holding the user credentials
	 * @param   array                   $options      Array of extra options
	 * @param   AuthenticationResponse  $response    Authentication response object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onUserAuthenticate(array $credentials, array $options, AuthenticationResponse $response);
}
