<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

defined('JPATH_PLATFORM') or die;

use Joomla\Authentication\Password\HandlerInterface;

/**
 * Password handler supporting testing against a chain of handlers
 *
 * @since  __DEPLOY_VERSION__
 */
class ChainedHandler implements HandlerInterface
{
	/**
	 * The password handlers in use by this chain.
	 *
	 * @var    HandlerInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $handlers = [];

	/**
	 * Add a handler to the chain
	 *
	 * @param   HandlerInterface  $handler  The password handler to add
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addHandler(HandlerInterface $handler)
	{
		$this->handlers[] = $handler;
	}

	/**
	 * Generate a hash for a plaintext password
	 *
	 * @param   string  $plaintext  The plaintext password to validate
	 * @param   array   $options    Options for the hashing operation
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function hashPassword($plaintext, array $options = [])
	{
		throw new \RuntimeException('The chained password handler cannot be used to hash a password');
	}

	/**
	 * Check that the password handler is supported in this environment
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Validate a password
	 *
	 * @param   string  $plaintext  The plain text password to validate
	 * @param   string  $hashed     The password hash to validate against
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function validatePassword($plaintext, $hashed)
	{
		foreach ($this->handlers as $handler)
		{
			if ($handler->isSupported() && $handler->validatePassword($plaintext, $hashed))
			{
				return true;
			}
		}

		return false;
	}
}
