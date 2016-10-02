<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Session\Validator;

use Joomla\Session\SessionInterface;
use Joomla\Session\ValidatorInterface;

/**
 * Interface for validating a part of the session
 *
 * @since  __DEPLOY_VERSION__
 */
class ForwardedValidator implements ValidatorInterface
{
	/**
	 * The input object.
	 *
	 * @var    \JInput
	 * @since  __DEPLOY_VERSION__
	 */
	private $input;

	/**
	 * The session object.
	 *
	 * @var    SessionInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $session;

	/**
	 * Constructor
	 *
	 * @param   \JInput           $input    The input object
	 * @param   SessionInterface  $session  The session object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\JInput $input, SessionInterface $session)
	{
		$this->input   = $input;
		$this->session = $session;
	}

	/**
	 * Validates the session throwing a SessionValidationException if there is an invalid property in the exception
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validate($restart = false)
	{
		if ($restart)
		{
			$this->session->set('session.client.forwarded', null);
		}

		$xForwardedFor = $this->input->server->getString('HTTP_X_FORWARDED_FOR', '');

		// Record proxy forwarded for in the session in case we need it later
		if (!empty($xForwardedFor) && filter_var($xForwardedFor, FILTER_VALIDATE_IP) !== false)
		{
			$this->session->set('session.client.forwarded', $xForwardedFor);
		}
	}
}
