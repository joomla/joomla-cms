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

use DirectoryIterator;
use stdClass;

/**
 * The Akeeba Engine configuration registry class
 */
class Configuration
{
	/**
	 * The currently loaded profile
	 *
	 * @var   integer
	 */
	public $activeProfile = null;

	/**
	 * Default namespace
	 *
	 * @var   string
	 */
	private $defaultNameSpace = 'global';

	/**
	 * Array keys which may contain stock directory definitions
	 *
	 * @var   array
	 */
	private $directory_containing_keys = [
		'akeeba.basic.output_directory',
	];

	/**
	 * Keys whose default values should never be overridden
	 *
	 * @var   array
	 */
	private $protected_nodes = [];

	/** The registry data
	 *
	 * @var   array
	 */
	private $registry = [];

	/**
	 * Constructor
	 *
	 * @return  void
	 */
	public function __construct()
	{
		// Create the default namespace
		$this->makeNameSpace($this->defaultNameSpace);

		// Create a default configuration
		$this->reset();
	}

	/**
	 * Create a namespace
	 *
	 * @param   string  $namespace  Name of the namespace to create
	 *
	 * @return  void
	 */
	public function makeNameSpace($namespace)
	{
		$this->registry[$namespace] = ['data' => new stdClass()];
	}

	/**
	 * Get the list of namespaces
	 *
	 * @return  array  List of namespaces
	 */
	public function getNameSpaces()
	{
		return array_keys($this->registry);
	}

	/**
	 * Get a registry value
	 *
	 * @param   string   $regpath               Registry path (e.g. global.directory.temporary)
	 * @param   mixed    $default               Optional default value
	 * @param   boolean  $process_special_vars  Optional. If true (default), it processes special variables, e.g.
	 *                                          [SITEROOT] in folder names
	 *
	 * @return  mixed  Value of entry or null
	 */
	public function get($regpath, $default = null, $process_special_vars = true)
	{
		// Cache the platform-specific stock directories
		static $stock_directories = [];

		if (empty($stock_directories))
		{
			$stock_directories = Platform::getInstance()->get_stock_directories();
		}

		$result = $default;

		// Explode the registry path into an array
		if ($nodes = explode('.', $regpath))
		{
			// Get the namespace
			$count = count($nodes);

			if ($count < 2)
			{
				$namespace = $this->defaultNameSpace;
				$nodes[1]  = $nodes[0];
			}
			else
			{
				$namespace = $nodes[0];
			}

			if (isset($this->registry[$namespace]))
			{
				$ns        = $this->registry[$namespace]['data'];
				$pathNodes = $count - 1;

				for ($i = 1; $i < $pathNodes; $i++)
				{
					if ((isset($ns->{$nodes[$i]})))
					{
						$ns = $ns->{$nodes[$i]};
					}
				}

				if (isset($ns->{$nodes[$i]}))
				{
					$result = $ns->{$nodes[$i]};
				}
			}
		}

		// Post-process certain directory-containing variables
		if ($process_special_vars && in_array($regpath, $this->directory_containing_keys))
		{
			if (!empty($stock_directories))
			{
				foreach ($stock_directories as $tag => $content)
				{
					$result = str_replace($tag, $content, $result);
				}
			}
		}

		return $result;
	}

	/**
	 * Set a registry value
	 *
	 * @param   string  $regpath               Registry Path (e.g. global.directory.temporary)
	 * @param   mixed   $value                 Value of entry
	 * @param   bool    $process_special_vars  Optional. If true (default), it processes special variables, e.g.
	 *                                         [SITEROOT] in folder names
	 *
	 * @return  mixed  Value of old value or boolean false if operation failed
	 */
	public function set($regpath, $value, $process_special_vars = true)
	{
		// Cache the platform-specific stock directories
		static $stock_directories = [];

		if (empty($stock_directories))
		{
			$stock_directories = Platform::getInstance()->get_stock_directories();
		}

		if (in_array($regpath, $this->protected_nodes))
		{
			return $this->get($regpath);
		}

		// Explode the registry path into an array
		$nodes = explode('.', $regpath);

		// Get the namespace
		$count = count($nodes);

		if ($count < 2)
		{
			$namespace = $this->defaultNameSpace;
		}
		else
		{
			$namespace = array_shift($nodes);
			$count--;
		}

		if (!isset($this->registry[$namespace]))
		{
			$this->makeNameSpace($namespace);
		}

		$ns = $this->registry[$namespace]['data'];

		$pathNodes = $count - 1;

		if ($pathNodes < 0)
		{
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i++)
		{
			// If any node along the registry path does not exist, create it
			if (!isset($ns->{$nodes[$i]}))
			{
				$ns->{$nodes[$i]} = new stdClass();
			}
			$ns = $ns->{$nodes[$i]};
		}

		// Set the new values
		if (is_string($value))
		{
			if (substr($value, 0, 10) == '###json###')
			{
				$value = json_decode(substr($value, 10));
			}
		}

		// Post-process certain directory-containing variables
		if ($process_special_vars && in_array($regpath, $this->directory_containing_keys))
		{
			if (!empty($stock_directories))
			{
				$data = $value;

				foreach ($stock_directories as $tag => $content)
				{
					$data = str_replace($tag, $content, $data);
				}

				$ns->{$nodes[$i]} = $data;

				return $ns->{$nodes[$i]};
			}
		}

		// This is executed if any of the previous two if's is false
		if (empty($nodes[$i]))
		{
			return false;
		}

		$ns->{$nodes[$i]} = $value;

		return $ns->{$nodes[$i]};
	}

	/**
	 * Unset (remove) a registry value
	 *
	 * @param   string  $regpath  Registry Path (e.g. global.directory.temporary)
	 *
	 * @return  boolean  True if the node was removed
	 */
	public function remove($regpath)
	{
		// Explode the registry path into an array
		$nodes = explode('.', $regpath);

		// Get the namespace
		$count = count($nodes);

		if ($count < 2)
		{
			$namespace = $this->defaultNameSpace;
		}
		else
		{
			$namespace = array_shift($nodes);
			$count--;
		}

		if (!isset($this->registry[$namespace]))
		{
			$this->makeNameSpace($namespace);
		}

		$ns = $this->registry[$namespace]['data'];

		$pathNodes = $count - 1;

		if ($pathNodes < 0)
		{
			$pathNodes = 0;
		}

		for ($i = 0; $i < $pathNodes; $i++)
		{
			// If any node along the registry path does not exist, return false
			if (!isset($ns->{$nodes[$i]}))
			{
				return false;
			}

			$ns = $ns->{$nodes[$i]};
		}

		unset($ns->{$nodes[$i]});

		return true;
	}

	/**
	 * Resets the registry to the default values
	 */
	public function reset()
	{
		// Load the Akeeba Engine INI files
		$root_path = __DIR__;

		$paths = [
			$root_path . '/Core',
			$root_path . '/Archiver',
			$root_path . '/Dump',
			$root_path . '/Scan',
			$root_path . '/Writer',
			$root_path . '/Proc',
			$root_path . '/Platform/Filter/Stack',
			$root_path . '/Filter/Stack',
		];

		$platform_paths = Platform::getInstance()->getPlatformDirectories();

		foreach ($platform_paths as $p)
		{
			$paths[] = $p . '/Filter/Stack';
			$paths[] = $p . '/Config';
		}

		foreach ($paths as $root)
		{
			if (!(is_dir($root) || is_link($root)))
			{
				continue;
			}

			if (!is_readable($root))
			{
				continue;
			}

			$di = new DirectoryIterator($root);

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				if ($file->getExtension() == 'json')
				{
					$this->mergeEngineJSON($file->getRealPath());
				}
			}
		}
	}

	/**
	 * Merges an associative array of key/value pairs into the registry.
	 * If noOverride is set, only non set or null values will be applied.
	 *
	 * @param   array  $array                 An associative array. Its keys are registry paths.
	 * @param   bool   $noOverride            [optional] Do not override pre-set values.
	 * @param   bool   $process_special_vars  Optional. If true (default), it processes special variables, e.g.
	 *                                        [SITEROOT] in folder names
	 */
	public function mergeArray($array, $noOverride = false, $process_special_vars = true)
	{
		if (!$noOverride)
		{
			foreach ($array as $key => $value)
			{
				$this->set($key, $value, $process_special_vars);
			}
		}
		else
		{
			foreach ($array as $key => $value)
			{
				if (is_null($this->get($key, null)))
				{
					$this->set($key, $value, $process_special_vars);
				}
			}
		}
	}

	/**
	 * Merges a JSON file into the registry. Its top level keys are registry paths,
	 * child keys are appended to the section-defined paths and then set equal to the
	 * values. If noOverride is set, only non set or null values will be applied.
	 * Top level keys beginning with an underscore will be ignored.
	 *
	 * @param   string   $jsonPath    The full path to the INI file to load
	 * @param   boolean  $noOverride  [optional] Do not override pre-set values.
	 *
	 * @return  boolean  True on success
	 */
	public function mergeJSON($jsonPath, $noOverride = false)
	{
		if (!file_exists($jsonPath))
		{
			return false;
		}

		$rawData  = file_get_contents($jsonPath);
		$jsonData = empty($rawData) ? [] : json_decode($rawData, true);

		foreach ($jsonData as $rootkey => $rootvalue)
		{
			if (!is_array($rootvalue))
			{
				if (!$noOverride)
				{
					$this->set($rootkey, $rootvalue);
				}
				elseif (is_null($this->get($rootkey, null)))
				{
					$this->set($rootkey, $rootvalue);
				}
			}
			elseif (substr($rootkey, 0, 1) != '_')
			{
				foreach ($rootvalue as $key => $value)
				{
					if (!$noOverride)
					{
						$this->set($rootkey . '.' . $key, $rootvalue);
					}
					elseif (is_null($this->get($rootkey . '.' . $key, null)))
					{
						$this->set($rootkey . '.' . $key, $rootvalue);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Merges an engine JSON file to the configuration. Each top level key defines a full
	 * registry path (section.subsection.key). It searches each top level key for the
	 * child key named "default" and merges its value to the configuration. The other keys
	 * are simply ignored.
	 *
	 * @param   string  $jsonPath    The absolute path to an JSON file
	 * @param   bool    $noOverride  [optional] If true, values from the JSON file will not override the configuration
	 *
	 * @return  boolean  True on success
	 */
	public function mergeEngineJSON($jsonPath, $noOverride = false)
	{
		if (!file_exists($jsonPath))
		{
			return false;
		}

		$rawData  = file_get_contents($jsonPath);
		$jsonData = empty($rawData) ? [] : json_decode($rawData, true);

		foreach ($jsonData as $section => $nodes)
		{
			if (is_array($nodes))
			{
				if (substr($section, 0, 1) != '_')
				{
					// Is this a protected node?
					$protected = false;

					if (array_key_exists('protected', $nodes))
					{
						$protected = $nodes['protected'];
					}

					// If overrides are allowed, unprotect until we can set the value
					if (!$noOverride)
					{
						if (in_array($section, $this->protected_nodes))
						{
							$pnk = array_search($section, $this->protected_nodes);
							unset($this->protected_nodes[$pnk]);
						}
					}

					if (array_key_exists('remove', $nodes))
					{
						// Remove a node if it has "remove" set
						$this->remove($section);
					}
					elseif (isset($nodes['default']))
					{
						if (!$noOverride)
						{
							// Update the default value if No Override is set
							$this->set($section, $nodes['default']);
						}
						elseif (is_null($this->get($section, null)))
						{
							// Set the default value if it does not exist
							$this->set($section, $nodes['default']);
						}
					}

					// Finally, if it's a protected node, enable the protection
					if ($protected)
					{
						$this->protected_nodes[] = $section;
					}
					else
					{
						$idx = array_search($section, $this->protected_nodes);

						if ($idx !== false)
						{
							unset($this->protected_nodes[$idx]);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Exports the current registry snapshot as a JSON-encoded object. Each namespace is a property of the top-level
	 * JSON object.
	 *
	 * @return  string  The JSON-encoded representation of the registry
	 *
	 * @since   6.4.1
	 */
	public function exportAsJSON()
	{
		$forJSON    = [];
		$namespaces = $this->getNameSpaces();

		foreach ($namespaces as $namespace)
		{
			if ($namespace == 'volatile')
			{
				continue;
			}

			$forJSON[$namespace] = $this->registry[$namespace]['data'];
		}

		return json_encode($forJSON, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);

	}

	/**
	 * Sets the protection status for a specific configuration key
	 *
	 * @param   string|array  $node     The node to protect/unprotect
	 * @param   boolean       $protect  True to protect, false to unprotect
	 *
	 * @return  void
	 */
	public function setKeyProtection($node, $protect = false)
	{
		if (is_array($node))
		{
			foreach ($node as $k)
			{
				$this->setKeyProtection($k, $protect);
			}
		}
		elseif (is_string($node))
		{
			if (is_array($this->protected_nodes))
			{
				$protected = in_array($node, $this->protected_nodes);
			}
			else
			{
				$this->protected_nodes = [];
				$protected             = false;
			}

			if ($protect)
			{
				if (!$protected)
				{
					$this->protected_nodes[] = $node;
				}
			}
			else
			{
				if ($protected)
				{
					$pnk = array_search($node, $this->protected_nodes);
					unset($this->protected_nodes[$pnk]);
				}
			}
		}
	}

	/**
	 * Returns a list of protected keys
	 *
	 * @return  array
	 */
	public function getProtectedKeys()
	{
		return $this->protected_nodes;
	}

	/**
	 * Resets the protected keys
	 *
	 * @return  void
	 */
	public function resetProtectedKeys()
	{
		$this->protected_nodes = [];
	}

	/**
	 * Sets the protected keys
	 *
	 * @param   array  $keys  A list of keys to protect
	 *
	 * @return  void
	 */
	public function setProtectedKeys($keys)
	{
		$this->protected_nodes = $keys;
	}
}
