<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  autoloader
 *  @copyright   Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * The main class autoloader for FOF itself
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFAutoloaderFof
{
	/**
	 * An instance of this autoloader
	 *
	 * @var   FOFAutoloaderFof
	 */
	public static $autoloader = null;

	/**
	 * The path to the FOF root directory
	 *
	 * @var   string
	 */
	public static $fofPath = null;

	/**
	 * Initialise this autoloader
	 *
	 * @return  FOFAutoloaderFof
	 */
	public static function init()
	{
		if (self::$autoloader == null)
		{
			self::$autoloader = new self;
		}

		return self::$autoloader;
	}

	/**
	 * Public constructor. Registers the autoloader with PHP.
	 */
	public function __construct()
	{
		self::$fofPath = realpath(__DIR__ . '/../');

		spl_autoload_register(array($this,'autoload_fof_core'));
	}

	/**
	 * The actual autoloader
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_core($class_name)
	{
		// Make sure the class has a FOF prefix
		if (substr($class_name, 0, 3) != 'FOF')
		{
			return;
		}

		// Remove the prefix
		$class = substr($class_name, 3);

		// Change from camel cased (e.g. ViewHtml) into a lowercase array (e.g. 'view','html')
		$class = preg_replace('/(\s)+/', '_', $class);
		$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
		$class = explode('_', $class);

		// First try finding in structured directory format (preferred)
		$path = self::$fofPath . '/' . implode('/', $class) . '.php';

		if (@file_exists($path))
		{
			include_once $path;
		}

		// Then try the duplicate last name structured directory format (not recommended)

		if (!class_exists($class_name, false))
		{
			reset($class);
			$lastPart = end($class);
			$path = self::$fofPath . '/' . implode('/', $class) . '/' . $lastPart . '.php';

			if (@file_exists($path))
			{
				include_once $path;
			}
		}

		// If it still fails, try looking in the legacy folder (used for backwards compatibility)

		if (!class_exists($class_name, false))
		{
			$path = self::$fofPath . '/legacy/' . implode('/', $class) . '.php';

			if (@file_exists($path))
			{
				include_once $path;
			}
		}
	}
}
