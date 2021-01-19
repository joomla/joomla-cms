<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Core;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Driver\Base as DriverBase;
use Akeeba\Engine\Driver\Mysqli;
use Akeeba\Engine\Platform;
use Exception;

/**
 * A utility class to return a database connection object
 */
class Database
{
	private static $instances = [];

	/**
	 * Returns a database connection object. It caches the created objects for future use.
	 *
	 * @param   array  $options  Options to use when instantiating the database connection
	 *
	 * @return DriverBase
	 */
	public static function &getDatabase($options, $unset = false)
	{
		if (!is_array(self::$instances))
		{
			self::$instances = [];
		}

		$signature = serialize($options);

		if ($unset)
		{
			if (!empty(self::$instances[$signature]))
			{
				$db = self::$instances[$signature];
				$db = null;
				unset(self::$instances[$signature]);
			}
			$null = null;

			return $null;
		}

		if (empty(self::$instances[$signature]))
		{
			$driver   = array_key_exists('driver', $options) ? $options['driver'] : '';
			$select   = array_key_exists('select', $options) ? $options['select'] : true;
			$database = array_key_exists('database', $options) ? $options['database'] : null;

			$driver = preg_replace('/[^A-Z0-9_\\\.-]/i', '', $driver);

			if (empty($driver))
			{
				// No driver specified; try to guess
				$default_signature = serialize(Platform::getInstance()->get_platform_database_options());
				if ($signature == $default_signature)
				{
					$driver = Platform::getInstance()->get_default_database_driver(true);
				}
				else
				{
					$driver = Platform::getInstance()->get_default_database_driver(false);
				}
			}
			else
			{
				// Make sure a full driver name was given
				if ((substr($driver, 0, 7) != '\\Akeeba') && substr($driver, 0, 7) != 'Akeeba\\')
				{
					$driver = '\\Akeeba\\Engine\\Driver\\' . ucfirst($driver);
				}
			}

			// Useful for PHP 7 which does NOT have the ancient mysql adapter
			if (($driver == '\\Akeeba\\Engine\\Driver\\Mysql') && !function_exists('mysql_connect'))
			{
				$driver = Mysqli::class;
			}

			self::$instances[$signature] = new $driver($options);
		}

		return self::$instances[$signature];
	}

	public static function unsetDatabase($options)
	{
		self::getDatabase($options, true);
	}
}
