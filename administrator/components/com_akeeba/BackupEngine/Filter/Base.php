<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Util\FileSystem;

abstract class Base
{
	/** @var string Filter's internal name; defaults to filename without .php extension */
	public $filter_name = '';

	/** @var string The filtering object: dir|file|dbobject|db */
	public $object = 'dir';

	/** @var string The filtering subtype (all|content|children|inclusion) */
	public $subtype = null;

	/** @var string The filtering method (direct|regex|api) */
	public $method = 'direct';

	/** @var bool Is the filter active? */
	public $enabled = true;

	/** @var array An array holding filter or regex strings per root, i.e. $filter_data[$root] = array() */
	protected $filter_data = null;

	/** @var FileSystem  Used by treatDirectory */
	protected $fsTools = null;

	/**
	 * Public constructor
	 */
	public function __construct()
	{
		// Set the filter name if it's missing (filename in lowercase, minus the .php extension)
		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}
	}

	/**
	 * Extra SQL statements to append to the SQL dump file. Useful for extension
	 * filters which have to filter out specific database records. This method
	 * must be overriden in children classes.
	 *
	 * @param   string  $root  The database for which to get the extra SQL statements
	 *
	 * @return  array  Extra SQL statements
	 */
	public function getExtraSQL(string $root): array
	{
		return [];
	}

	/**
	 * Returns filtering (exclusion) status of the $test object
	 *
	 * @param   string  $test     The string to check for filter status (e.g. filename, dir name, table name, etc)
	 * @param   string  $root     The exclusion root test belongs to
	 * @param   string  $object   What type of object is it? dir|file|dbobject
	 * @param   string  $subtype  Filter subtype (all|content|children)
	 *
	 * @return    bool    True if it excluded, false otherwise
	 */
	public function isFiltered($test, $root, $object, $subtype)
	{
		if (!$this->enabled)
		{
			return false;
		}

		//Factory::getLog()->log(LogLevel::DEBUG,"Filtering [$object:$subtype] $root // $test");

		// Inclusion filters do not qualify for exclusion
		if ($this->subtype == 'inclusion')
		{
			return false;
		}

		// The object and subtype must match
		if (($this->object != $object) || ($this->subtype != $subtype))
		{
			return false;
		}

		if (in_array($this->method, ['direct', 'regex']))
		{
			// -- Direct or regex based filters --

			// Get a local reference of the filter data, if necessary
			if (is_null($this->filter_data))
			{
				$filters           = Factory::getFilters();
				$this->filter_data = $filters->getFilterData($this->filter_name);
			}

			// Check if the root exists and if there's a filter for the $test
			if (!array_key_exists($root, $this->filter_data))
			{
				// Root not found
				return false;
			}
			else
			{
				// Root found, search in the array
				if ($this->method == 'direct')
				{
					// Direct filtering
					return in_array($test, $this->filter_data[$root]);
				}
				else
				{
					// Regex matching
					foreach ($this->filter_data[$root] as $regex)
					{
						if (substr($regex, 0, 1) == '!')
						{
							// Custom Akeeba Backup extension to PCRE notation. If you put a ! before the PCRE, it negates the result of the PCRE.
							if (!preg_match(substr($regex, 1), $test))
							{
								return true;
							}
						}
						else
						{
							// Normal PCRE
							if (preg_match($regex, $test))
							{
								return true;
							}
						}
					}

					// if we're here, no match exists
					return false;
				}
			}
		}
		else
		{
			// -- API-based filters --
			return $this->is_excluded_by_api($test, $root);
		}
	}

	/**
	 * Returns the inclusion filters defined by this class for the requested $object
	 *
	 * @param   string  $object  The object to get inclusions for (dir|db)
	 *
	 * @return    array    The inclusion filters
	 */
	public function &getInclusions($object)
	{
		$dummy = [];

		if (!$this->enabled)
		{
			return $dummy;
		}

		if (($this->subtype != 'inclusion') || ($this->object != $object))
		{
			return $dummy;
		}

		switch ($this->method)
		{
			case 'api':
				return $this->get_inclusions_by_api();
				break;

			case 'direct':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				return $this->filter_data;
				break;

			default:
				// regex inclusion is not supported at the moment
				$dummy = [];

				return $dummy;
				break;
		}
	}

	/**
	 * Adds an exclusion filter, or add/replace an inclusion filter
	 *
	 * @param   string  $root  Filter's root
	 * @param   mixed   $test  Exclusion: the filter string. Inclusion: the root definition data
	 *
	 * @return bool True on success
	 */
	public function set($root, $test)
	{
		if (in_array($this->subtype, ['all', 'content', 'children']))
		{
			return $this->setExclusion($root, $test);
		}
		else
		{
			return $this->setInclusion($root, $test);
		}
	}

	/**
	 * Unsets a given filter
	 *
	 * @param   string  $root  Filter's root
	 * @param   string  $test  The filter to remove
	 *
	 * @return bool
	 */
	public function remove($root, $test = null)
	{
		if ($this->subtype == 'inclusion')
		{
			return $this->removeInclusion($root);
		}
		else
		{
			return $this->removeExclusion($root, $test);
		}
	}

	/**
	 * Completely removes all filters off a specific root
	 *
	 * @param   string  $root
	 *
	 * @return bool
	 */
	public function reset($root)
	{
		switch ($this->method)
		{
			default:
			case 'api':
				return false;
				break;

			case 'direct':
			case 'regex':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}
				// Direct filters
				if (array_key_exists($root, $this->filter_data))
				{
					unset($this->filter_data[$root]);
				}
				else
				{
					// Root not found
					return false;
				}
				break;
		}

		$filters = Factory::getFilters();
		$filters->setFilterData($this->filter_name, $this->filter_data);

		return true;
	}

	/**
	 * Toggles a filter
	 *
	 * @param   string  $root        The filter root object
	 * @param   string  $test        The filter string to toggle
	 * @param   bool    $new_status  The new filter status after the operation (true: enabled, false: disabled)
	 *
	 * @return bool True on successful change, false if we failed to change it
	 */
	public function toggle($root, $test, &$new_status)
	{
		// Can't toggle inclusion filters!
		if ($this->subtype == 'inclusion')
		{
			return false;
		}

		$is_set     = $this->isFiltered($test, $root, $this->object, $this->subtype);
		$new_status = !$is_set;
		if ($is_set)
		{
			$status = $this->remove($root, $test);
		}
		else
		{
			$status = $this->set($root, $test);
		}
		if (!$status)
		{
			$new_status = $is_set;
		}

		return $status;
	}

	/**
	 * Does this class has any filters? If it doesn't, its methods are never called by
	 * Akeeba's engine to speed things up.
	 * @return bool
	 */
	public function hasFilters()
	{
		if (!$this->enabled)
		{
			return false;
		}

		switch ($this->method)
		{
			default:
			case 'api':
				// API filters always have data!
				return true;
				break;

			case 'direct':
			case 'regex':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				return !empty($this->filter_data);
				break;
		}
	}

	/**
	 * Returns a list of filter strings for the given root. Used by MySQLDump engine.
	 *
	 * @param   string  $root
	 *
	 * @return array
	 */
	public function getFilters($root)
	{
		$dummy = [];

		if (!$this->enabled)
		{
			return $dummy;
		}

		switch ($this->method)
		{
			default:
			case 'api':
				// API filters never have a list
				return $dummy;
				break;

			case 'direct':
			case 'regex':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				if (is_null($root))
				{
					// When NULL is passed as the root, we return all roots
					return $this->filter_data;
				}
				elseif (array_key_exists($root, $this->filter_data))
				{
					// The root exists, return its data
					return $this->filter_data[$root];
				}
				else
				{
					// The root doesn't exist, return an empty array
					return $dummy;
				}
				break;
		}
	}

	/**
	 * This method must be overriden by API-type exclusion filters.
	 *
	 * @param   string  $test  The object to test for exclusion
	 * @param   string  $root  The object's root
	 *
	 * @return    bool    Return true if it matches your filters
	 *
	 * @codeCoverageIgnore
	 */
	protected function is_excluded_by_api($test, $root)
	{
		return false;
	}

	/**
	 * This method must be overriden by API-type inclusion filters.
	 *
	 * @return    array    The inclusion filters
	 *
	 * @codeCoverageIgnore
	 */
	protected function &get_inclusions_by_api()
	{
		$dummy = [];

		return $dummy;
	}

	/**
	 * Remove the root prefix from an absolute path
	 *
	 * @param   string  $directory  The absolute path
	 *
	 * @return  string  The translated path, relative to the root directory of the backup job
	 */
	protected function treatDirectory($directory)
	{
		if (!is_object($this->fsTools))
		{
			$this->fsTools = Factory::getFilesystemTools();
		}

		// Get the site's root
		$configuration = Factory::getConfiguration();

		if ($configuration->get('akeeba.platform.override_root', 0))
		{
			$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');
		}
		else
		{
			$root = '[SITEROOT]';
		}

		if (stristr($root, '['))
		{
			$root = $this->fsTools->translateStockDirs($root);
		}

		$site_root = $this->fsTools->TrimTrailingSlash($this->fsTools->TranslateWinPath($root));

		$directory = $this->fsTools->TrimTrailingSlash($this->fsTools->TranslateWinPath($directory));

		// Trim site root from beginning of directory
		if (substr($directory, 0, strlen($site_root)) == $site_root)
		{
			$directory = substr($directory, strlen($site_root));

			if (substr($directory, 0, 1) == '/')
			{
				$directory = substr($directory, 1);
			}
		}

		return $directory;
	}

	/**
	 * Sets a filter, for direct and regex exclusion filter types
	 *
	 * @param   string  $root  The filter root object
	 * @param   string  $test  The filter string to set
	 *
	 * @return    bool    True on success
	 *
	 * @codeCoverageIgnore
	 */
	private function setExclusion($root, $test)
	{
		switch ($this->method)
		{
			default:
			case 'api':
				// we can't set new filter elements for API-type filters
				return false;
				break;

			case 'direct':
			case 'regex':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				// Direct filters
				if (array_key_exists($root, $this->filter_data))
				{
					if (!in_array($test, $this->filter_data[$root]))
					{
						$this->filter_data[$root][] = $test;
					}
					else
					{
						return false;
					}
				}
				else
				{
					$this->filter_data[$root] = [$test];
				}
				break;
		}

		$filters = Factory::getFilters();
		$filters->setFilterData($this->filter_name, $this->filter_data);

		return true;
	}

	/**
	 * Sets a filter, for direct inclusion filter types
	 *
	 * @param   string  $root  The inclusion filter key (root)
	 * @param   string  $test  The inclusion filter raw data
	 *
	 * @return    bool    True on success
	 *
	 * @codeCoverageIgnore
	 */
	private function setInclusion($root, $test)
	{
		switch ($this->method)
		{
			default:
			case 'api':
			case 'regex':
				// we can't set new filter elements for API or regex type filters
				return false;
				break;

			case 'direct':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				$this->filter_data[$root] = $test;
				break;
		}

		$filters = Factory::getFilters();
		$filters->setFilterData($this->filter_name, $this->filter_data);

		return true;
	}

	/**
	 * Remove a key from direct and regex filters
	 *
	 * @param   string  $root  The filter root object
	 * @param   string  $test  The filter string to set
	 *
	 * @return    bool    True on success
	 *
	 * @codeCoverageIgnore
	 */
	private function removeExclusion($root, $test)
	{
		switch ($this->method)
		{
			default:
			case 'api':
				// we can't remove filter elements from API-type filters
				return false;
				break;

			case 'direct':
			case 'regex':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				// Direct filters
				if (array_key_exists($root, $this->filter_data))
				{
					if (in_array($test, $this->filter_data[$root]))
					{
						if (count($this->filter_data[$root]) == 1)
						{
							// If it's the only element, remove the entire root key
							unset($this->filter_data[$root]);
						}
						else
						{
							// If there are more elements, remove just the $test value
							$key = array_search($test, $this->filter_data[$root]);
							unset($this->filter_data[$root][$key]);
						}
					}
					else
					{
						// Filter object not found
						return false;
					}
				}
				else
				{
					// Root not found
					return false;
				}
				break;
		}

		$filters = Factory::getFilters();
		$filters->setFilterData($this->filter_name, $this->filter_data);

		return true;
	}

	/**
	 * Remove an inclusion filter
	 *
	 * @param   string  $root  The root of the filter to remove
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore
	 */
	private function removeInclusion($root)
	{
		switch ($this->method)
		{
			default:
			case 'api':
			case 'regex':
				// we can't remove filter elements from API or regex type filters
				return false;
				break;

			case 'direct':
				// Get a local reference of the filter data, if necessary
				if (is_null($this->filter_data))
				{
					$filters           = Factory::getFilters();
					$this->filter_data = $filters->getFilterData($this->filter_name);
				}

				if (array_key_exists($root, $this->filter_data))
				{
					unset($this->filter_data[$root]);
				}
				else
				{
					// Root not found
					return false;
				}
				break;
		}

		$filters = Factory::getFilters();
		$filters->setFilterData($this->filter_name, $this->filter_data);

		return true;
	}
}
