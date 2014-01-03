<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Path Helper
 *
 * A static class help finding extension path.
 *
 * @package     Joomla.Libraries
 * @subpackage  Helper
 * @since       3.3
 */
class JHelperPath
{
	/**
	 * @var    array  The mapper to find extension type.
	 * @since  3.3
	 */
	protected static $extMapper = array(
		'com_' => 'components',
		'mod_' => 'modules',
		'plg_' => 'plugins',
		'lib_' => 'libraries',
		'tpl_' => 'templates'
	);

	/**
	 * Get the path of extension.
	 *
	 * @param   string   $element   The extension element name, example: com_content or plg_group_name
	 * @param   string   $client    Site or administrator.
	 * @param   boolean  $absolute  True to return whole path.
	 *
	 * @return  string  The found path.
	 *
	 * @since  3.3
	 */
	public static function get($element, $client = null, $absolute = true)
	{
		$element = strtolower($element);

		list($extension, $name, $group) = static::extractElement($element);

		$folder = $name;

		// Assign name path.
		switch ($extension)
		{
			case 'components':
			case 'modules':
				$folder = $element;
				break;

			case 'plugins':
				$folder = $group . '/' . $name;
				$client = 'site';
				break;

			case 'libraries':
				$client = 'site';
				break;

			default:
				$folder = $name;
				break;
		}

		// Build path
		$path = $extension . '/' . $folder;

		if (!$absolute)
		{
			return $path;
		}

		// Add absolute path.
		switch ($client)
		{
			case 'site':
				$path = JPATH_SITE . '/' . $path;
				break;

			case 'admin':
			case 'administrator':
				$path = JPATH_ADMINISTRATOR . '/' . $path;
				break;

			default:
				$path = JPATH_BASE . '/' . $path;
				break;
		}

		return $path;
	}

	/**
	 * Get path of administrator.
	 *
	 * @param   string   $element   The extension element name, example: com_content or plg_group_name
	 * @param   boolean  $absolute  True to return whole path.
	 *
	 * @return  string  The found path.
	 *
	 * @since   3.3
	 */
	public static function getAdmin($element, $absolute = true)
	{
		return static::get($element, 'administrator', $absolute);
	}

	/**
	 * Get path of front-end.
	 *
	 * @param   string   $element   The extension element name, example: com_content or plg_group_name
	 * @param   boolean  $absolute  True to return whole path.
	 *
	 * @return  string  The found path.
	 *
	 * @since   3.3
	 */
	public static function getSite($element, $absolute = true)
	{
		return static::get($element, 'site', $absolute);
	}

	/**
	 * Extract element.
	 *
	 * @param   string  $element  he extension element name, example: com_content or plg_group_name
	 *
	 * @return  array
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   3.3
	 */
	protected static function extractElement($element)
	{
		$prefix = substr($element, 0, 4);

		$ext = static::getExtName($prefix);

		if (!$ext)
		{
			throw new \InvalidArgumentException(sprintf('Need extension prefix, "%s" given.', $element));
		}

		$group = '';
		$name = substr($element, 4);

		// Get group
		if ($ext == 'plugins')
		{
			$name  = explode('_', $name);

			$group = array_shift($name);

			$name  = implode('_', $name);

			if (!$name)
			{
				throw new \InvalidArgumentException(sprintf('Plugin name need group, eg: "plg_group_name", "%s" given.', $element));
			}
		}

		return array($ext, $name, $group);
	}

	/**
	 * Get extension type name.
	 *
	 * @param   string  $prefix  The extension prefix.
	 *
	 * @return  string|null  Extension type name.
	 *
	 * @since   3.3
	 */
	protected static function getExtName($prefix)
	{
		if (!empty(static::$extMapper[$prefix]))
		{
			return static::$extMapper[$prefix];
		}

		return null;
	}
}
