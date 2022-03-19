<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

/**
 * Base class for a Joomla Model
 *
 * @since  4.0.0
 */
abstract class BaseModel extends CMSObject implements ModelInterface, StatefulModelInterface
{
	use StateBehaviorTrait;
	use LegacyModelLoaderTrait;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $name;

	/**
	 * The include paths
	 *
	 * @var   array
	 * @since  4.0.0
	 */
	protected static $paths;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, ignore_request).
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		// Set the view name
		if (empty($this->name))
		{
			if (\array_key_exists('name', $config))
			{
				$this->name = $config['name'];
			}
			else
			{
				$this->name = $this->getName();
			}
		}

		// Set the model state
		if (\array_key_exists('state', $config))
		{
			$this->state = $config['state'];
		}

		// Set the internal state marker - used to ignore setting state from the request
		if (!empty($config['ignore_request']))
		{
			$this->__state_set = true;
		}
	}

	/**
	 * Add a directory where \JModelLegacy should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 *
	 * @since       3.0
	 * @deprecated  5.0 See LegacyModelLoaderTrait\getInstance
	 */
	public static function addIncludePath($path = '', $prefix = '')
	{
		if (!isset(self::$paths))
		{
			self::$paths = array();
		}

		if (!isset(self::$paths[$prefix]))
		{
			self::$paths[$prefix] = array();
		}

		if (!isset(self::$paths['']))
		{
			self::$paths[''] = array();
		}

		if (!empty($path))
		{
			foreach ((array) $path as $includePath)
			{
				if (!\in_array($includePath, self::$paths[$prefix]))
				{
					array_unshift(self::$paths[$prefix], Path::clean($includePath));
				}

				if (!\in_array($includePath, self::$paths['']))
				{
					array_unshift(self::$paths[''], Path::clean($includePath));
				}
			}
		}

		return self::$paths[$prefix];
	}

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/Model(.*)/i', \get_class($this), $r))
			{
				throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_GET_NAME', __METHOD__), 500);
			}

			$this->name = str_replace(['\\', 'model'], '', strtolower($r[1]));
		}

		return $this->name;
	}
}
