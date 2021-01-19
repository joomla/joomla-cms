<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use DirectoryIterator;
use LogicException;

/**
 * Unified engine parameters helper class. Deals with scripting, GUI configuration elements and information on engine
 * parts (filters, dump engines, scan engines, archivers, installers).
 */
class EngineParameters
{
	/**
	 * Holds the parsed scripting.json contents
	 *
	 * @var array
	 */
	public $scripting = null;

	/**
	 * Holds the known paths holding JSON definitions of engines, installers and configuration gui elements
	 *
	 * @var  array
	 */
	protected $enginePartPaths = [];

	/**
	 * Cache of the engines known to this object
	 *
	 * @var array
	 */
	protected $engine_list = [];

	/**
	 * Cache of the GUI configuration elements known to this object
	 *
	 * @var array
	 */
	protected $gui_list = [];

	/**
	 * Cache of the installers known to this object
	 *
	 * @var array
	 */
	protected $installer_list = [];

	/**
	 * The currently active scripting type
	 *
	 * @var string
	 */
	protected $activeType = null;

	/**
	 * Loads the scripting.json and returns an array with the domains, the scripts and the raw data
	 *
	 * @return  array  The parsed scripting.json. Array keys: domains, scripts, data
	 */
	public function loadScripting($jsonPath = '')
	{
		if (!empty($this->scripting))
		{
			return $this->scripting;
		}

		$this->scripting = [];
		$jsonPath        = Factory::getAkeebaRoot() . '/Core/scripting.json';

		if (!@file_exists($jsonPath))
		{
			return $this->scripting;
		}

		$rawData          = file_get_contents($jsonPath);
		$rawScriptingData = empty($rawData) ? [] : json_decode($rawData, true);
		$domain_keys      = explode('|', $rawScriptingData['volatile.akeebaengine.domains']);
		$domains          = [];

		foreach ($domain_keys as $key)
		{
			$record        = [
				'domain' => $rawScriptingData['volatile.domain.' . $key . '.domain'],
				'class'  => $rawScriptingData['volatile.domain.' . $key . '.class'],
				'text'   => $rawScriptingData['volatile.domain.' . $key . '.text'],
			];
			$domains[$key] = $record;
		}

		$script_keys = explode('|', $rawScriptingData['volatile.akeebaengine.scripts']);
		$scripts     = [];

		foreach ($script_keys as $key)
		{
			$record        = [
				'chain' => explode('|', $rawScriptingData['volatile.scripting.' . $key . '.chain']),
				'text'  => $rawScriptingData['volatile.scripting.' . $key . '.text'],
			];
			$scripts[$key] = $record;
		}

		$this->scripting = [
			'domains' => $domains,
			'scripts' => $scripts,
			'data'    => $rawScriptingData,
		];

		return $this->scripting;
	}

	/**
	 * Imports the volatile scripting parameters to the registry
	 *
	 * @return  void
	 */
	public function importScriptingToRegistry()
	{
		$scripting     = $this->loadScripting();
		$configuration = Factory::getConfiguration();
		$configuration->mergeArray($scripting['data'], false);
	}

	/**
	 * Returns a volatile scripting parameter for the active backup type
	 *
	 * @param   string  $key      The relative key, e.g. core.createarchive
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  The scripting parameter's value
	 */
	public function getScriptingParameter($key, $default = null)
	{
		$configuration = Factory::getConfiguration();

		if (is_null($this->activeType))
		{
			$this->activeType = $configuration->get('akeeba.basic.backup_type', 'full');
		}

		return $configuration->get('volatile.scripting.' . $this->activeType . '.' . $key, $default);
	}

	/**
	 * Returns an array with domain keys and domain class names for the current
	 * backup type. The idea is that shifting this array walks through the backup
	 * process. When the array is empty, the backup is done.
	 *
	 * Each element of the array is an array with two keys: domain and class.
	 *
	 * @return  array
	 */
	public function getDomainChain()
	{
		$configuration = Factory::getConfiguration();
		$script        = $configuration->get('akeeba.basic.backup_type', 'full');

		$scripting = $this->loadScripting();
		$domains   = $scripting['domains'];
		$keys      = $scripting['scripts'][$script]['chain'];

		$result = [];

		foreach ($keys as $domain_key)
		{
			$result[] = [
				'domain' => $domains[$domain_key]['domain'],
				'class'  => $domains[$domain_key]['class'],
			];
		}

		return $result;
	}

	/**
	 * Append a path to the end of the paths list for a specific section
	 *
	 * @param   string  $path     Absolute filesystem path to add
	 * @param   string  $section  The section to add it to (gui, engine, installer, filters)
	 *
	 * @return  void
	 */
	public function addPath($path, $section = 'gui')
	{
		$path = Factory::getFilesystemTools()->TranslateWinPath($path);

		// If the array is empty, populate with the defaults
		if (!array_key_exists($section, $this->enginePartPaths))
		{
			$this->getEnginePartPaths($section);
		}

		// If the path doesn't already exist, add it
		if (!in_array($path, $this->enginePartPaths[$section]))
		{
			$this->enginePartPaths[$section][] = $path;
		}
	}

	/**
	 * Add a path to the beginning of the paths list for a specific section
	 *
	 * @param   string  $path     Absolute filesystem path to add
	 * @param   string  $section  The section to add it to (gui, engine, installer, filters)
	 *
	 * @return  void
	 */
	public function prependPath($path, $section = 'gui')
	{
		$path = Factory::getFilesystemTools()->TranslateWinPath($path);

		// If the array is empty, populate with the defaults
		if (!array_key_exists($section, $this->enginePartPaths))
		{
			$this->getEnginePartPaths($section);
		}

		// If the path doesn't already exist, add it
		if (!in_array($path, $this->enginePartPaths[$section]))
		{
			array_unshift($this->enginePartPaths[$section], $path);
		}
	}

	/**
	 * Get the paths for a specific section
	 *
	 * @param   string  $section  The section to get the path list for (engine, installer, gui, filter)
	 *
	 * @return  array
	 */
	public function getEnginePartPaths($section = 'gui')
	{
		// Create the key if it's not already present
		if (!array_key_exists($section, $this->enginePartPaths))
		{
			$this->enginePartPaths[$section] = [];
		}

		if (!empty($this->enginePartPaths[$section]))
		{
			return $this->enginePartPaths[$section];
		}

		// Add the defaults if the list is empty
		switch ($section)
		{
			case 'engine':
				$this->enginePartPaths[$section] = [
					Factory::getFilesystemTools()->TranslateWinPath(Factory::getAkeebaRoot()),
				];
				break;

			case 'installer':
				$this->enginePartPaths[$section] = [
					Factory::getFilesystemTools()->TranslateWinPath(Platform::getInstance()->get_installer_images_path()),
				];
				break;

			case 'gui':
				// Add core GUI definitions
				$this->enginePartPaths[$section] = [
					Factory::getFilesystemTools()->TranslateWinPath(Factory::getAkeebaRoot() . '/Core'),
				];

				// Add platform GUI definition files
				$platform_paths = Platform::getInstance()->getPlatformDirectories();

				foreach ($platform_paths as $p)
				{
					$this->enginePartPaths[$section][] = Factory::getFilesystemTools()->TranslateWinPath($p . '/Config');

					$pro = defined('AKEEBA_PRO') && AKEEBA_PRO;
					$pro = defined('AKEEBABACKUP_PRO') ? (AKEEBABACKUP_PRO ? true : false) : $pro;

					if ($pro)
					{
						$this->enginePartPaths[$section][] = Factory::getFilesystemTools()->TranslateWinPath($p . '/Config/Pro');
					}
				}
				break;

			case 'filter':
				$this->enginePartPaths[$section] = [
					Factory::getFilesystemTools()->TranslateWinPath(Factory::getAkeebaRoot() . '/Platform/Filter/Stack'),
					Factory::getFilesystemTools()->TranslateWinPath(Factory::getAkeebaRoot() . '/Filter/Stack'),
				];

				$platform_paths = Platform::getInstance()->getPlatformDirectories();

				foreach ($platform_paths as $p)
				{
					$this->enginePartPaths[$section][] = Factory::getFilesystemTools()->TranslateWinPath($p . '/Filter/Stack');
				}

				break;

			default:
				throw new LogicException(sprintf('Can not get paths for engine section ‘%s’. No section by this name is known to Akeeba Engine.', $section));
		}

		return $this->enginePartPaths[$section];
	}

	/**
	 * Returns a hash list of Akeeba engines and their data. Each entry has the engine name as key and contains two
	 * arrays, under the 'information' and 'parameters' keys.
	 *
	 * @param   string  $engine_type  The engine type to return information for
	 *
	 * @return  array
	 */
	public function getEnginesList($engine_type)
	{
		$engine_type = ucfirst($engine_type);

		// Try to serve cached data first
		if (isset($this->engine_list[$engine_type]))
		{
			return $this->engine_list[$engine_type];
		}

		// Find absolute path to normal and plugins directories
		$temp      = $this->getEnginePartPaths('engine');
		$path_list = [];

		foreach ($temp as $path)
		{
			$path_list[] = $path . '/' . $engine_type;
		}

		// Initialize the array where we store our data
		$this->engine_list[$engine_type] = [];

		// Loop for the paths where engines can be found
		foreach ($path_list as $path)
		{
			if (!@is_dir($path))
			{
				continue;
			}

			if (!@is_readable($path))
			{
				continue;
			}

			$di = new DirectoryIterator($path);

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				if ($file->getExtension() !== 'json')
				{
					continue;
				}

				$bare_name = ucfirst($file->getBasename('.json'));

				// Some hosts copy .json and .php files, renaming them (ie foobar.1.php)
				// We need to exclude them, otherwise we'll get a fatal error for declaring the same class twice
				if (preg_match('/[^A-Za-z0-9]/', $bare_name))
				{
					continue;
				}

				$information = [];
				$parameters  = [];

				$this->parseEngineJSON($file->getRealPath(), $information, $parameters);

				$this->engine_list[$engine_type][lcfirst($bare_name)] = [
					'information' => $information,
					'parameters'  => $parameters,
				];
			}
		}

		return $this->engine_list[$engine_type];
	}

	/**
	 * Parses the GUI JSON files and returns an array of groups and their data
	 *
	 * @return  array
	 */
	public function getGUIGroups()
	{
		// Try to serve cached data first
		if (!empty($this->gui_list) && is_array($this->gui_list))
		{
			if (count($this->gui_list) > 0)
			{
				return $this->gui_list;
			}
		}

		// Find absolute path to normal and plugins directories
		$path_list = $this->getEnginePartPaths('gui');

		// Initialize the array where we store our data
		$this->gui_list = [];

		// Loop for the paths where engines can be found
		foreach ($path_list as $path)
		{
			if (!@is_dir($path))
			{
				continue;
			}

			if (!@is_readable($path))
			{
				continue;
			}

			$allJSONFiles = [];
			$di           = new DirectoryIterator($path);

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				// PHP 5.3.5 and earlier do not support getExtension
				if ($file->getExtension() !== 'json')
				{
					continue;
				}

				$allJSONFiles[] = $file->getRealPath();
			}

			if (empty($allJSONFiles))
			{
				continue;
			}

			// Sort GUI files alphabetically
			asort($allJSONFiles);

			// Include each GUI def file
			foreach ($allJSONFiles as $filename)
			{
				$information = [];
				$parameters  = [];

				$this->parseInterfaceJSON($filename, $information, $parameters);

				// This effectively skips non-GUI JSONs (e.g. the scripting JSON)
				if (!empty($information['description']))
				{
					if (!isset($information['merge']))
					{
						$information['merge'] = 0;
					}

					$group_name = substr(basename($filename), 0, -5);

					$def = [
						'information' => $information,
						'parameters'  => $parameters,
					];

					if (!$information['merge'] || !isset($this->gui_list[$group_name]))
					{
						$this->gui_list[$group_name] = $def;
					}
					else
					{
						$this->gui_list[$group_name]['information'] = array_merge($this->gui_list[$group_name]['information'], $def['information']);
						$this->gui_list[$group_name]['parameters']  = array_merge($this->gui_list[$group_name]['parameters'], $def['parameters']);
					}
				}
			}
		}

		ksort($this->gui_list);

		// Push stack filter settings to the 03.filters section
		$path_list = $this->getEnginePartPaths('filter');

		// Loop for the paths where optional filters can be found
		foreach ($path_list as $path)
		{
			if (!@is_dir($path))
			{
				continue;
			}

			if (!@is_readable($path))
			{
				continue;
			}

			// Store JSON names in temp array because we'll sort based on filename (GUI order IS IMPORTANT!!)
			$allJSONFiles = [];

			$di = new DirectoryIterator($path);

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				// PHP 5.3.5 and earlier do not support getExtension
				if ($file->getExtension() !== 'json')
				{
					continue;
				}

				$allJSONFiles[] = $file->getRealPath();
			}

			if (empty($allJSONFiles))
			{
				continue;
			}

			// Sort filter files alphabetically
			asort($allJSONFiles);

			// Include each filter def file
			foreach ($allJSONFiles as $filename)
			{
				$information = [];
				$parameters  = [];

				$this->parseInterfaceJSON($filename, $information, $parameters);

				if (!array_key_exists('03.filters', $this->gui_list))
				{
					$this->gui_list['03.filters'] = ['parameters' => []];
				}

				if (!array_key_exists('parameters', $this->gui_list['03.filters']))
				{
					$this->gui_list['03.filters']['parameters'] = [];
				}

				if (!is_array($parameters))
				{
					$parameters = [];
				}

				$this->gui_list['03.filters']['parameters'] = array_merge($this->gui_list['03.filters']['parameters'], $parameters);
			}
		}

		return $this->gui_list;
	}

	/**
	 * Parses the installer JSON files and returns an array of installers and their data
	 *
	 * @param   boolean  $forDisplay  If true only returns the information relevant for displaying the GUI
	 *
	 * @return  array
	 */
	public function getInstallerList($forDisplay = false)
	{
		// Try to serve cached data first
		if (!empty($this->installer_list) && is_array($this->installer_list))
		{
			if (count($this->installer_list) > 0)
			{
				return $this->installer_list;
			}
		}

		// Find absolute path to normal and plugins directories
		$path_list = [
			Platform::getInstance()->get_installer_images_path(),
		];

		// Initialize the array where we store our data
		$this->installer_list = [];

		// Loop for the paths where engines can be found
		foreach ($path_list as $path)
		{
			if (!@is_dir($path))
			{
				continue;
			}

			if (!@is_readable($path))
			{
				continue;
			}

			$di = new DirectoryIterator($path);

			/** @var DirectoryIterator $file */
			foreach ($di as $file)
			{
				if (!$file->isFile())
				{
					continue;
				}

				// PHP 5.3.5 and earlier do not support getExtension
				if ($file->getExtension() !== 'json')
				{
					continue;
				}

				$rawData = file_get_contents($file->getRealPath());
				$data    = empty($rawData) ? [] : json_decode($rawData, true);

				if ($forDisplay)
				{
					$innerData = reset($data);

					if (array_key_exists('listinoptions', $innerData))
					{
						if ($innerData['listinoptions'] == 0)
						{
							continue;
						}
					}
				}

				foreach ($data as $key => $values)
				{
					$this->installer_list[$key] = [];

					foreach ($values as $key2 => $value)
					{
						$this->installer_list[$key][$key2] = $value;
					}
				}
			}
		}

		return $this->installer_list;
	}

	/**
	 * Returns the JSON representation of the GUI definition and the associated values
	 *
	 * @return   string
	 */
	public function getJsonGuiDefinition()
	{
		// Initialize the array which will be converted to JSON representation
		$json_array = [
			'engines'    => [],
			'installers' => [],
			'gui'        => [],
		];

		// Get a reference to the configuration
		$configuration = Factory::getConfiguration();

		// Get data for all engines
		$engine_types = [
			'archiver',
			'dump',
			'scan',
			'writer',
			'postproc',
		];

		foreach ($engine_types as $type)
		{
			$engines = $this->getEnginesList($type);

			$tempArray    = [];
			$engineTitles = [];

			foreach ($engines as $engine_name => $engine_data)
			{
				// Translate information
				foreach ($engine_data['information'] as $key => $value)
				{
					switch ($key)
					{
						case 'title':
						case 'description':
							$value = Platform::getInstance()->translate($value);
							break;
					}

					$tempArray[$engine_name]['information'][$key] = $value;

					if ($key == 'title')
					{
						$engineTitles[$engine_name] = $value;
					}
				}

				// Process parameters
				$parameters = [];

				foreach ($engine_data['parameters'] as $param_key => $param)
				{
					$param['default'] = $configuration->get($param_key, $param['default'], false);

					foreach ($param as $option_key => $option_value)
					{
						// Translate title, description, enumkeys
						switch ($option_key)
						{
							case 'title':
							case 'description':
							case 'labelempty':
							case 'labelnotempty':
								$param[$option_key] = Platform::getInstance()->translate($option_value);
								break;

							case 'enumkeys':
								$enumkeys = explode('|', $option_value);
								$new_keys = [];
								foreach ($enumkeys as $old_key)
								{
									$new_keys[] = Platform::getInstance()->translate($old_key);
								}
								$param[$option_key] = implode('|', $new_keys);
								break;

							default:
						}
					}

					$parameters[$param_key] = $param;
				}

				// Add processed parameters
				$tempArray[$engine_name]['parameters'] = $parameters;
			}

			asort($engineTitles);

			foreach ($engineTitles as $engineName => $title)
			{
				$json_array['engines'][$type][$engineName] = $tempArray[$engineName];
			}
		}

		// Get data for GUI elements
		$json_array['gui'] = [];
		$groupdefs         = $this->getGUIGroups();

		foreach ($groupdefs as $groupKey => $definition)
		{
			$group_name = '';

			if (isset($definition['information']) && isset($definition['information']['description']))
			{
				$group_name = Platform::getInstance()->translate($definition['information']['description']);
			}

			// Skip no-name groups
			if (empty($group_name))
			{
				continue;
			}

			$parameters = [];

			foreach ($definition['parameters'] as $param_key => $param)
			{
				$param['default'] = $configuration->get($param_key, $param['default'], false);

				foreach ($param as $option_key => $option_value)
				{
					// Translate title, description, enumkeys
					switch ($option_key)
					{
						case 'title':
						case 'description':
							$param[$option_key] = Platform::getInstance()->translate($option_value);
							break;

						case 'enumkeys':
							$enumkeys = explode('|', $option_value);
							$new_keys = [];
							foreach ($enumkeys as $old_key)
							{
								$new_keys[] = Platform::getInstance()->translate($old_key);
							}
							$param[$option_key] = implode('|', $new_keys);
							break;

						default:
					}
				}
				$parameters[$param_key] = $param;
			}
			$json_array['gui'][$group_name] = $parameters;
		}

		// Get data for the installers
		$json_array['installers'] = $this->getInstallerList(true);

		uasort($json_array['installers'], function ($a, $b) {
			if ($a['name'] == $b['name'])
			{
				return 0;
			}

			return ($a['name'] < $b['name']) ? -1 : 1;
		});

		$json = json_encode($json_array);

		return $json;
	}

	/**
	 * Parses an engine JSON file returning two arrays, one with the general information
	 * of that engine and one with its configuration variables' definitions
	 *
	 * @param   string  $jsonPath     Absolute path to engine JSON file
	 * @param   array   $information  [out] The engine information hash array
	 * @param   array   $parameters   [out] The parameters hash array
	 *
	 * @return  bool  True if the file was loaded
	 */
	public function parseEngineJSON($jsonPath, &$information, &$parameters)
	{
		if (!file_exists($jsonPath))
		{
			return false;
		}

		$information = [
			'title'       => '',
			'description' => '',
		];

		$parameters = [];

		$rawData  = file_get_contents($jsonPath);
		$jsonData = empty($rawData) ? [] : json_decode($rawData, true);

		foreach ($jsonData as $section => $data)
		{
			if (is_array($data))
			{
				if ($section == '_information')
				{
					// Parse information
					foreach ($data as $key => $value)
					{
						$information[$key] = $value;
					}
				}
				elseif (substr($section, 0, 1) != '_')
				{
					// Parse parameters
					$newparam = [
						'title'       => '',
						'description' => '',
						'type'        => 'string',
						'default'     => '',
					];

					foreach ($data as $key => $value)
					{
						$newparam[$key] = $value;
					}
					$parameters[$section] = $newparam;
				}
			}
		}

		return true;
	}

	/**
	 * Parses a graphical interface JSON file returning two arrays, one with the general
	 * information of that configuration section and one with its configuration variables'
	 * definitions.
	 *
	 * @param   string  $jsonPath     Absolute path to engine JSON file
	 * @param   array   $information  [out] The GUI information hash array
	 * @param   array   $parameters   [out] The parameters hash array
	 *
	 * @return bool True if the file was loaded
	 */
	public function parseInterfaceJSON($jsonPath, &$information, &$parameters)
	{
		if (!file_exists($jsonPath))
		{
			return false;
		}

		$information = [
			'description' => '',
		];

		$parameters = [];
		$rawData    = file_get_contents($jsonPath);
		$jsonData   = empty($rawData) ? [] : json_decode($rawData, true);

		foreach ($jsonData as $section => $data)
		{
			if (is_array($data))
			{
				if ($section == '_group')
				{
					// Parse information
					foreach ($data as $key => $value)
					{
						$information[$key] = $value;
					}

					continue;
				}

				if (substr($section, 0, 1) != '_')
				{
					// Parse parameters
					$newparam = [
						'title'       => '',
						'description' => '',
						'type'        => 'string',
						'default'     => '',
						'protected'   => 0,
					];

					foreach ($data as $key => $value)
					{
						$newparam[$key] = $value;
					}

					$parameters[$section] = $newparam;
				}
			}
		}

		return true;
	}
}
