<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Session\Validator;

use Joomla\Session\Exception\InvalidSessionException;
use Joomla\Session\SessionInterface;
use Joomla\Session\ValidatorInterface;

/**
 * Interface for validating a part of the session
 *
 * @since  __DEPLOY_VERSION__
 */
class AddressValidator implements ValidatorInterface
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
	 * @throws  InvalidSessionException
	 */
	public function validate($restart = false)
	{
		if ($restart)
		{
			$this->session->set('session.client.address', null);
		}

		$remoteAddr = $this->input->server->getString('REMOTE_ADDR', '');

		// Check for client address
		if (!empty($remoteAddr) && filter_var($remoteAddr, FILTER_VALIDATE_IP) !== false)
		{
			$ip = $this->session->get('session.client.address');

			if ($ip === null)
			{
				$this->session->set('session.client.address', $remoteAddr);
			}
			elseif ($remoteAddr !== $ip)
			{
				throw new InvalidSessionException('Invalid client IP');
			}
		}
	}
}
