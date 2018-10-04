<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * APC session storage handler for PHP
 *
 * @link        https://secure.php.net/manual/en/function.session-set-save-handler.php
 * @since       1.0
 * @deprecated  2.0  The Storage class chain will be removed.
 */
class Apc extends Storage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 * @deprecated  2.0
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new \RuntimeException('APC Extension is not available', 404);
		}

		parent::__construct($options);
	}

	/**
	 * Read the data for a particular session identifier from the
	 * SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public function read($id)
	{
		$sess_id = 'sess_' . $id;

		return (string) apc_fetch($sess_id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id            The session identifier.
	 * @param   string  $session_data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public function write($id, $session_data)
	{
		$sess_id = 'sess_' . $id;

		return apc_store($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public function destroy($id)
	{
		$sess_id = 'sess_' . $id;

		return apc_delete($sess_id);
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public static function isSupported()
	{
		return extension_loaded('apc');
	}
}
