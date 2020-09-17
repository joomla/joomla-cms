<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Autoloader;

defined('_JEXEC') || die;

use InvalidArgumentException;

/**
 * A PSR-4 class autoloader. This is a modified version of Composer's ClassLoader class
 *
 * @codeCoverageIgnore
 */
class Autoloader
{
	/** @var   Autoloader  The static instance of this autoloader */
	private static $instance;
	/** @var   array  Lengths of PSR-4 prefixes */
	private $prefixLengths = [];
	/** @var   array  Prefix to directory map */
	private $prefixDirs = [];
	/** @var   array  Fall-back directories */
	private $fallbackDirs = [];

	/**
	 * @return Autoloader
	 */
	public static function getInstance()
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new Autoloader();
		}

		return self::$instance;
	}

	/**
	 * Returns the prefix to directory map
	 *
	 * @return  array
	 *
	 * @noinspection PhpUnused
	 */
	public function getPrefixes()
	{
		return $this->prefixDirs;
	}

	/**
	 * Returns the list of fall=back directories
	 *
	 * @return  array
	 *
	 * @noinspection PhpUnused
	 */
	public function getFallbackDirs()
	{
		return $this->fallbackDirs;
	}

	/**
	 * Registers a set of PSR-4 directories for a given namespace, either
	 * appending or prefixing to the ones previously set for this namespace.
	 *
	 * @param   string        $prefix   The prefix/namespace, with trailing '\\'
	 * @param   array|string  $paths    The PSR-0 base directories
	 * @param   boolean       $prepend  Whether to prefix the directories
	 *
	 * @return  $this for chaining
	 *
	 * @throws  InvalidArgumentException  When the prefix is invalid
	 */
	public function addMap($prefix, $paths, $prepend = false)
	{
		if ($prefix)
		{
			$prefix = ltrim($prefix, '\\');
		}

		if (!$prefix)
		{
			// Register directories for the root namespace.
			if ($prepend)
			{
				$this->fallbackDirs = array_merge(
					(array) $paths,
					$this->fallbackDirs
				);

				$this->fallbackDirs = array_unique($this->fallbackDirs);
			}
			else
			{
				$this->fallbackDirs = array_merge(
					$this->fallbackDirs,
					(array) $paths
				);

				$this->fallbackDirs = array_unique($this->fallbackDirs);
			}
		}
		elseif (!isset($this->prefixDirs[$prefix]))
		{
			// Register directories for a new namespace.
			$length = strlen($prefix);
			if ('\\' !== $prefix[$length - 1])
			{
				throw new InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
			}
			$this->prefixLengths[$prefix[0]][$prefix] = $length;
			$this->prefixDirs[$prefix]                = (array) $paths;
		}
		elseif ($prepend)
		{
			// Prepend directories for an already registered namespace.
			$this->prefixDirs[$prefix] = array_merge(
				(array) $paths,
				$this->prefixDirs[$prefix]
			);

			$this->prefixDirs[$prefix] = array_unique($this->prefixDirs[$prefix]);
		}
		else
		{
			// Append directories for an already registered namespace.
			$this->prefixDirs[$prefix] = array_merge(
				$this->prefixDirs[$prefix],
				(array) $paths
			);

			$this->prefixDirs[$prefix] = array_unique($this->prefixDirs[$prefix]);
		}

		return $this;
	}

	/**
	 * Does the autoloader have a map for the specified prefix?
	 *
	 * @param   string  $prefix
	 *
	 * @return  bool
	 */
	public function hasMap($prefix)
	{
		return isset($this->prefixDirs[$prefix]);
	}

	/**
	 * Registers a set of PSR-4 directories for a given namespace,
	 * replacing any others previously set for this namespace.
	 *
	 * @param   string        $prefix  The prefix/namespace, with trailing '\\'
	 * @param   array|string  $paths   The PSR-4 base directories
	 *
	 * @return  void
	 *
	 * @throws  InvalidArgumentException  When the prefix is invalid
	 *
	 * @noinspection PhpUnused
	 */
	public function setMap($prefix, $paths)
	{
		if ($prefix)
		{
			$prefix = ltrim($prefix, '\\');
		}

		if (!$prefix)
		{
			$this->fallbackDirs = (array) $paths;
		}
		else
		{
			$length = strlen($prefix);
			if ('\\' !== $prefix[$length - 1])
			{
				throw new InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
			}
			$this->prefixLengths[$prefix[0]][$prefix] = $length;
			$this->prefixDirs[$prefix]                = (array) $paths;
		}
	}

	/**
	 * Registers this instance as an autoloader.
	 *
	 * @param   boolean  $prepend  Whether to prepend the autoloader or not
	 *
	 * @return  void
	 */
	public function register($prepend = false)
	{
		spl_autoload_register([$this, 'loadClass'], true, $prepend);
	}

	/**
	 * Unregisters this instance as an autoloader.
	 *
	 * @return  void
	 */
	public function unregister()
	{
		spl_autoload_unregister([$this, 'loadClass']);
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param   string  $class  The name of the class
	 *
	 * @return  bool True if loaded, null otherwise
	 */
	public function loadClass($class)
	{
		if ($file = $this->findFile($class))
		{
			/** @noinspection PhpIncludeInspection */
			include $file;

			return true;
		}

		return false;
	}

	/**
	 * Finds the path to the file where the class is defined.
	 *
	 * @param   string  $class  The name of the class
	 *
	 * @return  string|false  The path if found, false otherwise
	 */
	public function findFile($class)
	{
		// work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
		if ('\\' == $class[0])
		{
			$class = substr($class, 1);
		}

		// FEFHelper lookup
		if (substr($class, 0, 9) == 'FEFHelper')
		{

			if (file_exists($file = realpath(__DIR__ . '/..') . '/Utils/FEFHelper/' . strtolower(substr($class, 9)) . '.php'))
			{
				return $file;
			}
		}

		// PSR-4 lookup
		$logicalPath = strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';

		$first = $class[0];

		if (isset($this->prefixLengths[$first]))
		{
			foreach ($this->prefixLengths[$first] as $prefix => $length)
			{
				if (0 === strpos($class, $prefix))
				{
					foreach ($this->prefixDirs[$prefix] as $dir)
					{
						if (file_exists($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPath, $length)))
						{
							return $file;
						}
					}
				}
			}
		}

		// PSR-4 fallback dirs
		foreach ($this->fallbackDirs as $dir)
		{
			if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPath))
			{
				return $file;
			}
		}

		return false;
	}
}

// Register the current namespace with the autoloader
Autoloader::getInstance()->addMap('FOF30\\', [realpath(__DIR__ . '/..')]);
Autoloader::getInstance()->register();
