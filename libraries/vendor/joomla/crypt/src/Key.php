<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt;

/**
 * Encryption key object for the Joomla Framework.
 *
 * @since  1.0
 */
class Key
{
	/**
	 * The private key.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $private;

	/**
	 * The public key.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $public;

	/**
	 * The key type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $type;

	/**
	 * Constructor.
	 *
	 * @param   string  $type     The key type.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 *
	 * @since   1.0
	 */
	public function __construct($type, $private, $public)
	{
		// Set the key type.
		$this->type = (string) $type;

		// Set the public/private key strings.
		$this->private = (string) $private;
		$this->public  = (string) $public;
	}

	/**
	 * Retrieve the private key
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPrivate()
	{
		return $this->private;
	}

	/**
	 * Retrieve the public key
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPublic()
	{
		return $this->public;
	}

	/**
	 * Retrieve the key type
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getType()
	{
		return $this->type;
	}
}
