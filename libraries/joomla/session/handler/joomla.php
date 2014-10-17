<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Interface for managing HTTP sessions
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @since       3.4
 */
class JSessionHandlerJoomla extends JSessionHandlerNative
{
	/**
	 * The input object
	 *
	 * @var    JInput
	 * @since  3.4
	 */
	private $input = null;

	/**
	 * Public constructor
	 *
	 * @param  JInput  $input  The input object
	 *
	 * @since  3.4
	 */
	public function __construct($input)
	{
		$this->input = $input;

		// Disable transparent sid support
		ini_set('session.use_trans_sid', '0');

		// Only allow the session ID to come from cookies and nothing else.
		ini_set('session.use_only_cookies', '1');
	}

	/**
	 * Starts the session.
	 *
	 * @return  bool  True if started.
	 *
	 * @throws RuntimeException If something goes wrong starting the session.
	 */
	public function start()
	{
		$session_name = $this->getName();

		// Get the JInputCookie object
		$cookie = $this->input->cookie;

		if (is_null($cookie->get($session_name)))
		{
			$session_clean = $this->input->get($session_name, false, 'string');

			if ($session_clean)
			{
				$this->setId($session_clean);
				$cookie->set($session_name, '', time() - 3600);
			}
		}

		return parent::start();
	}
}
