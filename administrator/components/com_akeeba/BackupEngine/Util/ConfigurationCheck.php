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

/**
 * Quirk detection helper class
 */
class ConfigurationCheck
{
	/**
	 * The configuration checks to perform
	 *
	 * @var  array
	 */
	protected $configurationChecks = [
		['code'        => '001', 'severity' => 'critical', 'callback' => [null, 'q001'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q001',
		],
		['code'        => '003', 'severity' => 'critical', 'callback' => [null, 'q003'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q003',
		],
		['code'        => '004', 'severity' => 'critical', 'callback' => [null, 'q004'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q004',
		],

		['code'        => '101', 'severity' => 'high', 'callback' => [null, 'q101'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q101',
		],
		['code'        => '103', 'severity' => 'high', 'callback' => [null, 'q103'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q103',
		],
		['code'        => '104', 'severity' => 'high', 'callback' => [null, 'q104'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q104',
		],
		['code'        => '106', 'severity' => 'high', 'callback' => [null, 'q106'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q106',
		],

		['code'        => '201', 'severity' => 'medium', 'callback' => [null, 'q201'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q201',
		],
		['code'        => '202', 'severity' => 'medium', 'callback' => [null, 'q202'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q202',
		],
		['code'        => '204', 'severity' => 'medium', 'callback' => [null, 'q204'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q204',
		],

		['code'        => '203', 'severity' => 'medium', 'callback' => [null, 'q203'],
		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q203',
		],
//		['code'        => '401', 'severity' => 'low', 'callback' => [null, 'q401'],
//		 'description' => 'COM_AKEEBA_CPANEL_WARNING_Q401',
//		],
	];

	/**
	 * The public constructor replaces the missing object reference in the configuration check callbacks
	 */
	function __construct()
	{
		$temp = [];

		foreach ($this->configurationChecks as $check)
		{
			$check['callback'] = [$this, $check['callback'][1]];
			$temp[]            = $check;
		}

		$this->configurationChecks = $temp;
	}

	/**
	 * Returns the output & temporary folder writable status
	 *
	 * @return  array  A hash array with the writable status
	 */
	public function getFolderStatus()
	{
		static $status = null;

		if (is_null($status))
		{
			$stock_dirs = Platform::getInstance()->get_stock_directories();

			// Get output writable status
			$registry = Factory::getConfiguration();
			$outdir   = $registry->get('akeeba.basic.output_directory');

			foreach ($stock_dirs as $macro => $replacement)
			{
				$outdir = str_replace($macro, $replacement, $outdir);
			}

			$status['output'] = @is_writable($outdir);
		}

		return $status;
	}

	/**
	 * Returns the overall status. It's true when both the temporary and output directories are writable and there are
	 * no critical configuration check failures.
	 *
	 * @return  boolean
	 */
	public function getShortStatus()
	{
		// Base the status on directory writeable status
		$status = $this->getFolderStatus();
		$ret    = $status['output'];

		// Scan for high severity configuration check errors
		$detailedStatus = $this->getDetailedStatus();

		if (!empty($detailedStatus))
		{
			foreach ($detailedStatus as $configCheck)
			{
				if ($configCheck['severity'] == 'critical')
				{
					$ret = false;
				}
			}
		}

		// Return status
		return $ret;
	}

	/**
	 * Add a configuration check definition
	 *
	 * @param   string  $code         The configuration check code (three digit number)
	 * @param   string  $severity     The severity (low, medium, high, critical)
	 * @param   string  $description  The description key for this configuration check
	 * @param   null    $callback     The callback used to determine the status of the configuration check
	 *
	 * @return  void
	 */
	public function addConfigurationCheckDefinition($code, $severity = 'low', $description = null, $callback = null)
	{
		if (!is_callable($callback))
		{
			$callback = [$this, 'q' . $code];
		}

		if (empty($description))
		{
			$description = 'COM_AKEEBA_CPANEL_WARNING_Q' . $code;
		}

		$newConfigurationCheck = [
			'code'        => $code,
			'severity'    => $severity,
			'description' => $description,
			'callback'    => $callback,
		];

		$this->configurationChecks[$code] = $newConfigurationCheck;
	}

	/**
	 * Remove a configuration check definition
	 *
	 * @param   string  $code  The code of the configuration check to remove
	 *
	 * @return  void
	 */
	public function removeConfigurationCheckDefinition($code)
	{
		if (isset($this->configurationChecks[$code]))
		{
			unset($this->configurationChecks[$code]);
		}
	}

	/**
	 * Clear the configuration check definitions
	 *
	 * @return  void
	 */
	public function clearConfigurationCheckDefinitions()
	{
		$this->configurationChecks = [];
	}

	/**
	 * Runs the configuration check scripts. These are potential problems related to server
	 * configuration, out of Akeeba's control. They are intended to give the user a
	 * chance to fix them before they cause the backup to fail.
	 *
	 * Numbering scheme:
	 * Q0xx    No-go errors
	 * Q1xx    Critical system configuration errors
	 * Q2xx    Medium and low system configuration warnings
	 * Q3xx    Critical software configuration errors
	 * Q4xx    Medium and low component configuration warnings
	 *
	 * @param   boolean  $low_priority       Should I include low priority quirks?
	 * @param   string   $help_url_template  The sprintf template from creating a help URL from a config check code
	 *
	 * @return  array
	 */
	public function getDetailedStatus($low_priority = false, $help_url_template = 'https://www.akeeba.com/documentation/warnings/q%s.html')
	{
		static $detailedStatus = null;

		if (is_null($detailedStatus) || $low_priority)
		{
			$detailedStatus = [];

			foreach ($this->configurationChecks as $quirkDef)
			{
				if (!$low_priority && ($quirkDef['severity'] == 'low'))
				{
					continue;
				}

				$this->checkConfiguration($detailedStatus, $quirkDef, $help_url_template);
			}
		}

		return $detailedStatus;
	}

	/**
	 * Checks if a path is restricted by open_basedirs
	 *
	 * @param   string  $check  The path to check
	 *
	 * @return  bool  True if the path is restricted (which is bad)
	 */
	public function checkOpenBasedirs($check)
	{
		static $paths;

		if (empty($paths))
		{
			$open_basedir = ini_get('open_basedir');

			if (empty($open_basedir))
			{
				return false;
			}

			$delimiter  = strpos($open_basedir, ';') !== false ? ';' : ':';
			$paths_temp = explode($delimiter, $open_basedir);

			// Some open_basedirs are using environemtn variables
			$paths = [];

			foreach ($paths_temp as $path)
			{
				if (array_key_exists($path, $_ENV))
				{
					$paths[] = $_ENV[$path];
				}
				else
				{
					$paths[] = $path;
				}
			}
		}

		if (empty($paths))
		{
			return false; // no restrictions
		}
		else
		{
			$newcheck = @realpath($check); // Resolve symlinks, like PHP does

			if (!($newcheck === false))
			{
				$check = $newcheck;
			}

			$included = false;

			foreach ($paths as $path)
			{
				$newpath = @realpath($path);

				if (!($newpath === false))
				{
					$path = $newpath;
				}

				if (strlen($check) >= strlen($path))
				{
					// Only check if the path to check is longer than the inclusion path.
					// Otherwise, I guarantee it's not included!!
					// If the path to check begins with an inclusion path, it's permitted. Easy, huh?
					if (substr($check, 0, strlen($path)) == $path)
					{
						$included = true;
					}
				}
			}

			return !$included;
		}
	}

	/**
	 * Make a configuration check and adds it to the list if it raises a warning / error
	 *
	 * @param   array   $detailedStatus     The configuration checks status array
	 * @param   array   $quirkDef           The configuration check definition
	 * @param   string  $help_url_template  The sprintf template from creating a help URL from a quirk code
	 *
	 * @return  void
	 */
	protected function checkConfiguration(&$detailedStatus, $quirkDef, $help_url_template)
	{
		if (call_user_func($quirkDef['callback']))
		{
			$description = Platform::getInstance()->translate($quirkDef['description']);

			$detailedStatus[(string) $quirkDef['code']] = [
				'code'        => $quirkDef['code'],
				'severity'    => $quirkDef['severity'],
				'description' => $description,
				'help_url'    => sprintf($help_url_template, $quirkDef['code']),
			];
		}
	}

	/**
	 * Q001 - HIGH - Output directory unwriteable
	 *
	 * @return  bool
	 */
	private function q001()
	{
		$status = $this->getFolderStatus();

		return !$status['output'];
	}

	/**
	 * Q003 - HIGH - Backup output or temporary set to site's root
	 *
	 * @return  bool
	 */
	private function q003()
	{
		$stock_dirs = Platform::getInstance()->get_stock_directories();

		$registry = Factory::getConfiguration();
		$outdir   = $registry->get('akeeba.basic.output_directory');

		foreach ($stock_dirs as $macro => $replacement)
		{
			$outdir = str_replace($macro, $replacement, $outdir);
		}

		$outdir_real = @realpath($outdir);

		if (!empty($outdir_real))
		{
			$outdir = $outdir_real;
		}

		$siteroot      = Platform::getInstance()->get_site_root();
		$siteroot_real = @realpath($siteroot);

		if (!empty($siteroot_real))
		{
			$siteroot = $siteroot_real;
		}

		return ($siteroot == $outdir);
	}

	/**
	 * Q004 - HIGH - Free memory too low
	 *
	 * @return bool
	 */
	private function q004()
	{
		// If we can't figure this out, don't report a problem. It doesn't
		// really matter, as the backup WILL crash eventually.
		if (!function_exists('ini_get'))
		{
			return false;
		}

		$memLimit = ini_get("memory_limit");
		$memLimit = $this->_return_bytes($memLimit);

		if ($memLimit <= 0)
		{
			return false;
		}

		// No limit?
		$availableRAM = $memLimit - memory_get_usage();

		// We need at least 12Mb of free memory
		return ($availableRAM <= (12 * 1024 * 1024));
	}

	/**
	 * Q101 - HIGH - open_basedir on output directory
	 *
	 * @return  bool
	 */
	private function q101()
	{
		$stock_dirs = Platform::getInstance()->get_stock_directories();

		// Get output writable status
		$registry = Factory::getConfiguration();
		$outdir   = $registry->get('akeeba.basic.output_directory');

		foreach ($stock_dirs as $macro => $replacement)
		{
			$outdir = str_replace($macro, $replacement, $outdir);
		}

		return $this->checkOpenBasedirs($outdir);
	}

	/**
	 * Q103 - HIGH - Less than 10" of max_execution_time with PHP Safe Mode enabled
	 *
	 * @return  bool
	 */
	private function q103()
	{
		$exectime = ini_get('max_execution_time');
		$safemode = ini_get('safe_mode');

		if (!$safemode)
		{
			return false;
		}

		if (!is_numeric($exectime))
		{
			return false;
		}

		if ($exectime <= 0)
		{
			return false;
		}

		return $exectime < 10;
	}

	/**
	 * Q104 - HIGH - Temp directory is the same as the site's root
	 *
	 * @return  bool
	 */
	private function q104()
	{

		$siteroot      = Platform::getInstance()->get_site_root();
		$siteroot_real = @realpath($siteroot);

		if (!empty($siteroot_real))
		{
			$siteroot = $siteroot_real;
		}

		$stockDirs      = Platform::getInstance()->get_stock_directories();
		$temp_directory = $stockDirs['[SITETMP]'];
		$temp_directory = @realpath($temp_directory);

		if (empty($temp_directory))
		{
			$temp_directory = $siteroot;
		}

		return ($siteroot == $temp_directory);
	}

	/**
	 * Q106 - HIGH  - Table name prefix contains uppercase characters
	 *
	 * @return  bool
	 */
	private function q106()
	{
		$filters   = Factory::getFilters();
		$databases = $filters->getInclusions('db');

		foreach ($databases as $db)
		{
			if (!isset($db['prefix']))
			{
				continue;
			}

			if (preg_match('/[A-Z]/', $db['prefix']))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Q201 - MEDIUM - Outdated PHP version.
	 *
	 * We currently check for PHP lower than 7.3.
	 *
	 * @return  bool
	 */
	private function q201()
	{
		return version_compare(PHP_VERSION, '7.3.0', 'lt');
	}

	/**
	 * Q202 - MED  - CRC problems with hash extension not present
	 *
	 * @return  bool
	 */
	private function q202()
	{
		$registry = Factory::getConfiguration();
		$archiver = $registry->get('akeeba.advanced.archiver_engine');

		if ($archiver != 'zip')
		{
			return false;
		}

		return !function_exists('hash_file');
	}

	/**
	 * Q203 - MED  - Default output directory in use
	 *
	 * @return  bool
	 */
	private function q203()
	{
		$stock_dirs = Platform::getInstance()->get_stock_directories();

		$registry = Factory::getConfiguration();
		$outdir   = $registry->get('akeeba.basic.output_directory');

		foreach ($stock_dirs as $macro => $replacement)
		{
			$outdir = str_replace($macro, $replacement, $outdir);
		}

		$default = $stock_dirs['[DEFAULT_OUTPUT]'];

		$outdir  = Factory::getFilesystemTools()->TranslateWinPath($outdir);
		$default = Factory::getFilesystemTools()->TranslateWinPath($default);

		return $outdir == $default;
	}

	/**
	 * Q204 - MED  - Disabled functions may affect operation
	 *
	 * @return  bool
	 */
	private function q204()
	{
		$disabled = ini_get('disabled_functions');

		return (!empty($disabled));
	}

	/**
	 * Q401 - LOW  - ZIP format selected
	 *
	 * @return  bool
	 */
	private function q401()
	{
		$registry = Factory::getConfiguration();
		$archiver = $registry->get('akeeba.advanced.archiver_engine');

		return $archiver == 'zip';
	}

	private function _return_bytes($setting)
	{
		$val  = trim($setting);
		$last = strtolower(substr($val, -1));
		$val  = substr($val, 0, -1);

		if (is_numeric($last))
		{
			return $setting;
		}

		switch ($last)
		{
			case 't':
				$val *= 1024;
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return (int) $val;
	}
}
