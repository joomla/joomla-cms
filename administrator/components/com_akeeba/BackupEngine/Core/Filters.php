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

use Akeeba\Engine\Factory;
use Akeeba\Engine\Filter\Base as FilterBase;
use Akeeba\Engine\Platform;
use DirectoryIterator;
use RuntimeException;

/**
 * Akeeba filtering feature
 */
class Filters
{
	/** @var array An array holding data for all defined filters */
	private $filter_registry = [];

	/** @var array Hash array with instances of all filters as $filter_name => filter_object */
	private $filters = [];

	/** @var bool True after the filter clean up has run */
	private $cleanup_has_run = false;

	/**
	 * Public constructor, loads filter data and filter classes
	 */
	public function __construct()
	{
		// Load filter data from platform's database
		Factory::getLog()->debug('Fetching filter data from database');
		$this->filter_registry = Platform::getInstance()->load_filters();

		// Load platform, plugin and core filters
		$this->filters = [];

		$locations = [
			Factory::getAkeebaRoot() . '/Filter',
		];

		$platform_paths = Platform::getInstance()->getPlatformDirectories();

		foreach ($platform_paths as $p)
		{
			$locations[] = $p . '/Filter';
		}

		Factory::getLog()->debug('Loading filters');

		foreach ($locations as $folder)
		{
			if (!@is_dir($folder))
			{
				continue;
			}

			if (!@is_readable($folder))
			{
				continue;
			}

			$di = new DirectoryIterator($folder);

			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				// PHP 5.3.5 and earlier do not support getExtension
				if ($file->getExtension() != 'php')
				{
					continue;
				}

				$filename = $file->getFilename();

				// Skip filter files starting with dot or dash
				if (in_array(substr($filename, 0, 1), ['.', '_']))
				{
					continue;
				}

				// Some hosts copy .json and .php files, renaming them (ie foobar.1.php)
				// We need to exclude them, otherwise we'll get a fatal error for declaring the same class twice
				$bare_name = $file->getBasename('.php');

				if (preg_match('/[^a-zA-Z0-9]/', $bare_name))
				{
					continue;
				}

				// Extract filter base name
				$filter_name = ucfirst($bare_name);

				// This is an abstract class; do not try to create instance
				if ($filter_name == 'Base')
				{
					continue;
				}

				// Skip already loaded filters
				if (array_key_exists($filter_name, $this->filters))
				{
					continue;
				}

				Factory::getLog()->debug('-- Loading filter ' . $filter_name);

				// Add the filter
				$this->filters[$filter_name] = Factory::getFilterObject($filter_name);
			}
		}

		// Load platform, plugin and core stacked filters
		$locations = [
			Factory::getAkeebaRoot() . '/Filter/Stack',
		];

		$platform_paths       = Platform::getInstance()->getPlatformDirectories();
		$platform_stack_paths = [];

		foreach ($platform_paths as $p)
		{
			$locations[]            = $p . '/Filter';
			$locations[]            = $p . '/Filter/Stack';
			$platform_stack_paths[] = $p . '/Filter/Stack';
		}

		$config = Factory::getConfiguration();
		Factory::getLog()->debug('Loading optional filters');

		foreach ($locations as $folder)
		{
			if (!@is_dir($folder))
			{
				continue;
			}

			if (!@is_readable($folder))
			{
				continue;
			}

			$di = new DirectoryIterator($folder);

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				// PHP 5.3.5 and earlier do not support getExtension
				// if ($file->getExtension() != 'php')
				if (substr($file->getBasename(), -4) != '.php')
				{
					continue;
				}

				// Some hosts copy .json and .php files, renaming them (ie foobar.1.php)
				// We need to exclude them, otherwise we'll get a fatal error for declaring the same class twice
				$bare_name = strtolower($file->getBasename('.php'));

				if (preg_match('/[^A-Za-z0-9]/', $bare_name))
				{
					continue;
				}

				// Extract filter base name
				if (substr($bare_name, 0, 5) == 'stack')
				{
					$bare_name = substr($bare_name, 5);
				}

				$filter_name = 'Stack\\Stack' . ucfirst($bare_name);

				// Skip already loaded filters
				if (array_key_exists($filter_name, $this->filters))
				{
					continue;
				}

				// Make sure the JSON file also exists
				if (!file_exists($folder . '/' . $bare_name . '.json'))
				{
					continue;
				}

				$key = "core.filters.$bare_name.enabled";

				if ($config->get($key, 0))
				{
					Factory::getLog()->debug('-- Loading optional filter ' . $filter_name);
					// Add the filter
					$this->filters[$filter_name] = Factory::getFilterObject($filter_name);
				}
			}
		}
	}

	/**
	 * Extended filtering information of a given object. Applies only to exclusion filters.
	 *
	 * @param   string|array  $test       The string to check for filter status (e.g. filename, dir name, table name, etc)
	 * @param   string        $root       The exclusion root test belongs to
	 * @param   string        $object     What type of object is it? dir|file|dbobject
	 * @param   string        $subtype    Filter subtype (all|content|children)
	 * @param   string        $by_filter  [out] The filter name which first matched $test, or an empty string
	 *
	 * @return  bool  True if it is a filtered element
	 */
	public function isFilteredExtended($test, $root, $object, $subtype, &$by_filter)
	{
		if (!$this->cleanup_has_run)
		{
			// Loop the filters and clean up those with no data
			/**
			 * @var string     $filter_name
			 * @var FilterBase $filter
			 */
			foreach ($this->filters as $filter_name => $filter)
			{
				if (!$filter->hasFilters())
				{
					unset($this->filters[$filter_name]);
				} // Remove empty filters
			}
			$this->cleanup_has_run = true;
		}

		$by_filter = '';
		if (!empty($this->filters))
		{
			foreach ($this->filters as $filter_name => $filter)
			{
				if ($filter->isFiltered($test, $root, $object, $subtype))
				{
					$by_filter = strtolower($filter_name);

					return true;
				}
			}

			// If we are still here, no filter matched
			return false;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the filtering status of a given object
	 *
	 * @param   string|array  $test     The string to check for filter status (e.g. filename, dir name, table name, etc)
	 * @param   string        $root     The exclusion root test belongs to
	 * @param   string        $object   What type of object is it? dir|file|dbobject
	 * @param   string        $subtype  Filter subtype (all|content|children)
	 *
	 * @return  bool  True if it is a filtered element
	 */
	public function isFiltered($test, $root, $object, $subtype)
	{
		$by_filter = '';

		return $this->isFilteredExtended($test, $root, $object, $subtype, $by_filter);
	}

	/**
	 * Returns the inclusion filters for a specific object type
	 *
	 * @param   string  $object  The inclusion object (dir|db)
	 *
	 * @return array
	 */
	public function &getInclusions($object)
	{
		$inclusions = [];

		if (!empty($this->filters))
		{
			/**
			 * @var string     $filter_name
			 * @var FilterBase $filter
			 */
			foreach ($this->filters as $filter_name => $filter)
			{
				if (!is_object($filter))
				{
					throw new RuntimeException("Object for filter $filter_name not found. The engine will now crash.");
				}

				$new_inclusions = $filter->getInclusions($object);

				if (!empty($new_inclusions))
				{
					$inclusions = array_merge($inclusions, $new_inclusions);
				}
			}
		}

		return $inclusions;
	}

	/**
	 * Returns the filter registry information for a specified filter class
	 *
	 * @param   string  $filter_name  The name of the filter we want data for
	 *
	 * @return    array    The filter data for the requested filter
	 */
	public function &getFilterData($filter_name)
	{
		if (array_key_exists($filter_name, $this->filter_registry))
		{
			return $this->filter_registry[$filter_name];
		}
		else
		{
			$dummy = [];

			return $dummy;
		}
	}

	/**
	 * Replaces the filter data of a specific filter with the new data
	 *
	 * @param   string  $filter_name  The filter for which to modify the stored data
	 * @param   string  $data         The new data
	 */
	public function setFilterData($filter_name, &$data)
	{
		$this->filter_registry[$filter_name] = $data;
	}

	/**
	 * Saves all filters to the platform defined database
	 *
	 * @return bool    True on success
	 */
	public function save()
	{
		return Platform::getInstance()->save_filters($this->filter_registry);
	}

	/**
	 * Get SQL statements to append to the database backup file
	 *
	 * @param   string  $root
	 *
	 * @return  array
	 */
	public function getExtraSQL(string $root): array
	{
		if (count($this->filters) < 1)
		{
			return [];
		}

		$ret = [];

		/**
		 * @var FilterBase $filter
		 */
		foreach ($this->filters as $filter)
		{
			$ret = array_merge($ret, $filter->getExtraSQL($root));
		}

		return $ret;
	}

	/**
	 * Checks if there is an active filter for the object/subtype requested.
	 *
	 * @param   string  $object   The filtering object: dir|file|dbobject|db
	 * @param   string  $subtype  The filtering subtype: all|content|children|inclusion
	 *
	 * @return bool
	 */
	public function hasFilterType($object, $subtype = null)
	{
		foreach ($this->filters as $filter_name => $filter)
		{
			if ($filter->object == $object)
			{
				if (is_null($subtype))
				{
					return true;
				}
				elseif ($filter->subtype == $subtype)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Resets all filters, reverting them to a blank state
	 *
	 * @return  void
	 *
	 * @since   5.4.0
	 */
	public function reset()
	{
		$this->filter_registry = [];
	}
}
