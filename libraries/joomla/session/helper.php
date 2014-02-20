<?php
/**
 * @package     Joomla.Platform
 * @subpackage  User
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
 * @since       11.1
 *
 * @author	Frits van Campen (mailto:Frits.vanCampen@moxio.com)
 * @see	{@link http://www.php.net/manual/en/function.session-decode.php#108037}
 */
abstract class JSessionHelper
{
	/**
	 * Method to detect and call the available {@link http://www.php.net/manual/en/session.configuration.php#ini.session.serialize-handler}
	 * session.serialize_handler
	 *
	 * @static
	 * @param	string	$session_data	The session data to process
	 * @throws	RuntimeException
	 * @return	multitype:mixed
	 */
	public static function unserialize($session_data)
	{
		$handler = strtolower(ini_get("session.serialize_handler"));

		switch ($handler)
		{
			case "php" :
				return static::unserialize_php($session_data);
			break;

			case "php_binary" :
				return static::unserialize_phpbinary($session_data);
			break;

			default :
				throw new RuntimeException(JText::sprintf('COM_KEY2SWAP_SESSION_HANDLER_ERROR_UNSUPPORTED_HANDLER', $handler));
		}
	}

	/* (non-PHPdoc)
	 * @static
	 * @param	string	$session_data	The session data to process
	 * @throws	RuntimeException
	 * @return	multitype:mixed
	 */
	private static function unserialize_php($session_data)
	{
		$return_data = array();
		$offset      = 0;

		while ($offset < strlen($session_data))
		{
			if (! strstr(substr($session_data, $offset), "|"))
			{
				throw new RuntimeException(JText::sprintf('COM_KEY2SWAP_SESSION_HANDLER_ERROR_INVALID_REMAINING_DATA', substr($session_data, $offset)));
			}

			$pos     = strpos($session_data, "|", $offset);
			$length  = $pos - $offset;
			$varname = substr($session_data, $offset, $length);
			$offset += $length + 1;
			$data    = unserialize(substr($session_data, $offset));

			$return_data[$varname] = $data;

			$offset += strlen(serialize($data));
		}

		return $return_data;
	}

	/* (non-PHPdoc)
	 * @static
	 * @param	string	$session_data	The session data to process
	 * @return	multitype:mixed
	 */
	private static function unserialize_phpbinary($session_data)
	{
		$return_data = array();
		$offset      = 0;

		while ($offset < strlen($session_data))
		{
			$length  = ord($session_data[$offset]);
			$offset += 1;
			$varname = substr($session_data, $offset, $length);
			$offset += $length;
			$data    = unserialize(substr($session_data, $offset));

			$return_data[$varname] = $data;

			$offset += strlen(serialize($data));
		}

		return $return_data;
	}

}
