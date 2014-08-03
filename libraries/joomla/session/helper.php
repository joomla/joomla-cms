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
 * Session helper class that helps to decode a database session data string into a human readable object.
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @since       3.4
 *
 * @author      Frits van Campen <Frits.vanCampen@moxio.com>
 * @link        http://www.php.net/manual/en/function.session-decode.php#108037
 */
abstract class JSessionHelper
{
	/**
	 * Method to detect and call the available {@link http://www.php.net/manual/en/session.configuration.php#ini.session.serialize-handler}
	 * session.serialize_handler
	 *
	 * @param   string  $session_data  The session data to process
	 *
	 * @return  mixed
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public static function unserialize($session_data)
	{
		$handler = strtolower(ini_get('session.serialize_handler'));

		switch ($handler)
		{
			case 'php' :
				return static::unserialize_php($session_data);

			case 'php_binary' :
				return static::unserialize_phpbinary($session_data);

			case 'wddx' : // TODO
			default :
				throw new RuntimeException(JText::sprintf('JLIB_SESSION_ERROR_UNSUPPORTED_HANDLER', $handler));
		}
	}

	/**
	 * Unserializes the session data for the PHP session serialize handler
	 *
	 * @param   string  $session_data  The session data to process
	 *
	 * @return  array
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	private static function unserialize_php($session_data)
	{
		$return_data = array();
		$offset      = 0;

		while ($offset < strlen($session_data))
		{
			if (!strstr(substr($session_data, $offset), '|'))
			{
				throw new RuntimeException(JText::sprintf('JLIB_SESSION_ERROR_INVALID_REMAINING_DATA', substr($session_data, $offset)));
			}

			$pos     = strpos($session_data, '|', $offset);
			$length  = $pos - $offset;
			$varname = substr($session_data, $offset, $length);
			$offset  += $length + 1;
			$data    = unserialize(substr($session_data, $offset));

			$return_data[$varname] = $data;

			$offset += strlen(serialize($data));
		}

		return $return_data;
	}

	/**
	 * Unserializes the session data for the PHP binary session serialize handler
	 *
	 * @param   string  $session_data  The session data to process
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	private static function unserialize_phpbinary($session_data)
	{
		$return_data = array();
		$offset      = 0;

		while ($offset < strlen($session_data))
		{
			$length  = ord($session_data[$offset]);
			$offset  += 1;
			$varname = substr($session_data, $offset, $length);
			$offset  += $length;
			$data    = unserialize(substr($session_data, $offset));

			$return_data[$varname] = $data;

			$offset += strlen(serialize($data));
		}

		return $return_data;
	}

	/**
	 * Unserializes the session data for the WDDX session serialize handler
	 *
	 * @param   string  $session_data  The session data to process
	 *
	 * @return  array
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	private static function unserialize_wddx($session_data)
	{
		// TODO
	}
}
