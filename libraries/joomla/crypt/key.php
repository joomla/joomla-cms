<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Encryption key[/pair] for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Crypt
 * @since       12.1
 */
class JCryptKey
{
	/**
	 * @var    string  The public key.
	 * @since  12.1
	 */
	public $public;

	/**
	 * @var    string  The private key.
	 * @since  12.1
	 */
	public $private;

	/**
	 * @var    string  The key type.
	 * @since  12.1
	 */
	public $type;
}
