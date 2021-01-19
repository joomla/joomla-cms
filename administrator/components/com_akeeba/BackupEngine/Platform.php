<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Platform\Base;
use Akeeba\Engine\Platform\PlatformInterface;
use DirectoryIterator;
use Exception;

/**
 * Platform abstraction. Manages the loading of platform connector objects and delegates calls to itself the them.
 *
 * @property string $tableNameProfiles The name of the table where backup profiles are stored
 * @property string $tableNameStats    The name of the table where backup records are stored
 *
 * @since    3.4
 */
class Platform
{
	/** @var Base|null The currently loaded platform connector object instance */
	protected static $platformConnectorInstance = null;

	/** @var array A list of additional directories where platform classes can be found */
	protected static $knownPlatformsDirectories = [];

	/** @var Platform The currently loaded object instance of this class. WARNING: This is NOT the platform connector! */
	protected static $instance = null;

	/**
	 * Public class constructor
	 *
	 * @param   string  $platform  Optional; platform name. Leave blank to auto-detect.
	 *
	 * @throws  Exception  When the platform cannot be loaded
	 */
	public function __construct($platform = null)
	{
		if (empty($platform) || is_null($platform))
		{
			$platform = static::detectPlatform();
		}

		if (empty($platform))
		{
			throw new Exception('Can not find a suitable Akeeba Engine platform for your site');
		}

		static::$platformConnectorInstance = static::loadPlatform($platform);

		if (!is_object(static::$platformConnectorInstance))
		{
			throw new Exception("Can not load Akeeba Engine platform $platform");
		}
	}

	/**
	 * Implements the Singleton pattern for this class
	 *
	 * @staticvar Platform $instance The static object instance
	 *
	 * @param   string  $platform  Optional; platform name. Autodetect if blank.
	 *
	 * @return PlatformInterface
	 */
	public static function &getInstance($platform = null)
	{
		if (!is_object(static::$instance))
		{
			static::$instance = new Platform($platform);
		}

		return static::$instance;
	}

	/**
	 * Get a list of all directories where platform classes can be found
	 *
	 * @return  array
	 */
	public static function getPlatformDirectories()
	{
		$defaultPath = [];

		if (is_object(static::$platformConnectorInstance))
		{
			$defaultPath[] = __DIR__ . '/Platform/' . static::$platformConnectorInstance->platformName;
		}

		return array_merge(
			static::$knownPlatformsDirectories,
			$defaultPath
		);
	}

	/**
	 * Lists available platforms
	 *
	 * @staticvar   array   $platforms   Static cache of the available platforms
	 *
	 * @return  array  The list of available platforms
	 */
	static public function listPlatforms()
	{
		if (empty(static::$knownPlatformsDirectories))
		{
			$di = new DirectoryIterator(__DIR__ . '/Platform');

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isDir())
				{
					continue;
				}

				if ($file->isDot())
				{
					continue;
				}

				if ($file->getExtension() !== 'php')
				{
					continue;
				}

				$shortName = $file->getFilename();
				$bareName  = basename($shortName, '.php');

				/**
				 * We never have dots in our filenames but some hosts will rename files similar to  foo.1.php when their
				 * broken security scanners detect a false positive. This is our defence against that.
				 */
				if (strpos($bareName, '.') !== false)
				{
					continue;
				}

				static::$knownPlatformsDirectories[$shortName] = $file->getRealPath();
			}
		}

		return static::$knownPlatformsDirectories;
	}

	/**
	 * Add a platform to the list of known platforms
	 *
	 * @param   string  $slug               Short name of the platform
	 * @param   string  $platformDirectory  The path where you can find it
	 *
	 * @return  void
	 */
	public static function addPlatform($slug, $platformDirectory)
	{
		if (empty(static::$knownPlatformsDirectories))
		{
			static::listPlatforms();

			static::$knownPlatformsDirectories[$slug] = $platformDirectory;
		}
	}

	/**
	 * Auto-detect the suitable platform for this site
	 *
	 * @return  string
	 *
	 * @throws  Exception  When no platform is detected
	 */
	protected static function detectPlatform()
	{
		$platforms = static::listPlatforms();

		if (empty($platforms))
		{
			throw new Exception('No Akeeba Engine platform class found');
		}

		$bestPlatform = (object) [
			'name'     => null,
			'priority' => 0,
		];

		foreach ($platforms as $platform => $path)
		{
			$o = static::loadPlatform($platform, $path);

			if (is_null($o))
			{
				continue;
			}

			if ($o->isThisPlatform())
			{
				if ($o->priority > $bestPlatform->priority)
				{
					$bestPlatform->priority = $o->priority;
					$bestPlatform->name     = $platform;
				}
			}
		}

		return $bestPlatform->name;
	}

	/**
	 * Load a given platform and return the platform object
	 *
	 * @param   string  $platform  Platform name
	 * @param   string  $path      The path to laod the platform from (optional)
	 *
	 * @return  Base
	 */
	protected static function &loadPlatform($platform, $path = null)
	{
		if (empty($path))
		{
			if (isset(static::$knownPlatformsDirectories[$platform]))
			{
				$path = static::$knownPlatformsDirectories[$platform];
			}
		}

		if (empty($path))
		{
			$path = dirname(__FILE__) . '/' . $platform;
		}

		$classFile = $path . '/Platform.php';
		$className = '\\Akeeba\\Engine\\Platform\\' . ucfirst($platform);

		$null = null;

		if (!file_exists($classFile))
		{
			return $null;
		}

		require_once($classFile);

		if (!class_exists($className, false))
		{
			return $null;
		}

		$o = new $className;

		return $o;
	}

	/**
	 * Magic method to proxy all calls to the loaded platform object
	 *
	 * @param   string  $name       The name of the method to call
	 * @param   array   $arguments  The arguments to pass
	 *
	 * @return  mixed  The result of the method being called
	 *
	 * @throws  Exception  When the platform isn't loaded or an non-existent method is called
	 */
	public function __call($name, array $arguments)
	{
		if (is_null(static::$platformConnectorInstance))
		{
			throw new Exception('Akeeba Engine platform is not loaded');
		}

		if (method_exists(static::$platformConnectorInstance, $name))
		{
			// Call_user_func_array is ~3 times slower than direct method calls.
			// See the on-line PHP documentation page of call_user_func_array for more information.
			switch (count($arguments))
			{
				case 0 :
					$result = static::$platformConnectorInstance->$name();
					break;
				case 1 :
					$result = static::$platformConnectorInstance->$name($arguments[0]);
					break;
				case 2:
					$result = static::$platformConnectorInstance->$name($arguments[0], $arguments[1]);
					break;
				case 3:
					$result = static::$platformConnectorInstance->$name($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$result = static::$platformConnectorInstance->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$result = static::$platformConnectorInstance->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					// Resort to using call_user_func_array for many segments
					$result = call_user_func_array([static::$platformConnectorInstance, $name], $arguments);
			}

			return $result;
		}
		else
		{
			throw new Exception('Method ' . $name . ' not found in Akeeba Platform');
		}
	}

	/**
	 * Magic getter for the properties of the loaded platform
	 *
	 * @param   string  $name  The name of the property to get
	 *
	 * @return  mixed  The value of the property
	 */
	public function __get($name)
	{
		if (!isset(static::$platformConnectorInstance->$name) || !property_exists(static::$platformConnectorInstance, $name))
		{
			static::$platformConnectorInstance->$name = null;
			user_error(__CLASS__ . ' does not support property ' . $name, E_NOTICE);
		}

		return static::$platformConnectorInstance->$name;
	}

	/**
	 * Magic setter for the properties of the loaded platform
	 *
	 * @param   string  $name   The name of the property to set
	 * @param   mixed   $value  The value of the property to set
	 */
	public function __set($name, $value)
	{
		if (isset(static::$platformConnectorInstance->$name) || property_exists(static::$platformConnectorInstance, $name))
		{
			static::$platformConnectorInstance->$name = $value;
		}
		else
		{
			static::$platformConnectorInstance->$name = null;
			user_error(__CLASS__ . ' does not support property ' . $name, E_NOTICE);
		}
	}
}
