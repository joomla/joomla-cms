<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Mixin\Chmod;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Model\Model;

/**
 * ConfigurationWizard model. Contains the business logic for the configuration wizard.
 */
class ConfigurationWizard extends Model
{
	use Chmod;

	/**
	 * Attempts to automatically figure out where the output and temporary directories should point, adjusting their
	 * permissions should it be necessary.
	 *
	 * @param   int  $dontRecurse  Used internally. Always skip this parameter when calling this method.
	 *
	 * @return  bool  True if we could fix the directories
	 */
	public function autofixDirectories($dontRecurse = 0)
	{
		// Get the profile ID
		$profile_id = Platform::getInstance()->get_active_profile();

		// Get the output and temporary directory
		$engineConfig = Factory::getConfiguration();
		$outputDirectory   = $engineConfig->get('akeeba.basic.output_directory', '');

		$fixOut  = true;

		if (is_dir($outputDirectory))
		{
			// Test the writability of the directory
			$filename = $outputDirectory . '/test.dat';
			$fixOut   = !@file_put_contents($filename, 'test');

			if (!$fixOut)
			{
				// Directory writable, remove the temp file
				@unlink($filename);
			}
			else
			{
				// Try to chmod the directory
				$this->chmod($outputDirectory, 511);
				// Repeat the test
				$fixOut = !@file_put_contents($filename, 'test');

				if (!$fixOut)
				{
					// Directory writable, remove the temp file
					@unlink($filename);
				}
			}
		}

		// Do I have to fix the output directory?
		if ($fixOut && ($dontRecurse < 1))
		{
			$engineConfig->set('akeeba.basic.output_directory', '[DEFAULT_OUTPUT]');
			Platform::getInstance()->save_configuration($profile_id);

			// After fixing the directory, run ourselves again
			return $this->autofixDirectories(1);
		}
		elseif ($fixOut)
		{
			// If we reached this point after recursing, we can't fix the permissions
			// and the user has to RTFM and fix the issue!
			return false;
		}

		return true;
	}

	/**
	 * Creates a temporary file of a specific size
	 *
	 * @param   int          $blocks How many 128Kb blocks to write. Common values: 1, 2, 4, 16, 40, 80, 81
	 * @param   string|null  $tempdir
	 *
	 * @return  bool  TRUE on success
	 */
	public function createTempFile($blocks = 1, $tempdir = null)
	{
		if (empty($tempdir))
		{
			$aeconfig = Factory::getConfiguration();
			$tempdir  = $aeconfig->get('akeeba.basic.output_directory', '');
		}

		$sixtyfourBytes = '012345678901234567890123456789012345678901234567890123456789ABCD';
		$oneKilo        = '';
		$oneBlock       = '';

		for ($i = 0; $i < 16; $i ++)
		{
			$oneKilo .= $sixtyfourBytes;
		}

		for ($i = 0; $i < 128; $i ++)
		{
			$oneBlock .= $oneKilo;
		}

		$filename = tempnam($tempdir, 'confwiz');
		@unlink($filename);

		$fp = @fopen($filename, 'w');

		if ($fp !== false)
		{
			for ($i = 0; $i < $blocks; $i ++)
			{
				if (!@fwrite($fp, $oneBlock))
				{
					@fclose($fp);
					@unlink($filename);

					return false;
				}
			}

			@fclose($fp);
			@unlink($filename);
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Sleeps for a given amount of time. Returns false if the sleep time requested is over the maximum execution time.
	 *
	 * @param   int  $secondsDelay  Seconds to sleep
	 *
	 * @return  bool  FALSE if we cannot sleep that long
	 */
	public function doNothing($secondsDelay = 1)
	{
		// Try to get the maximum execution time and PHP memory limit
		if (function_exists('ini_get'))
		{
			$maxexec  = ini_get("max_execution_time");
			$memlimit = ini_get("memory_limit");
		}
		else
		{
			$maxexec  = 14;
			$memlimit = 16777216;
		}

		// Unknown time limit; suppose 10s
		if (!is_numeric($maxexec) || ($maxexec == 0))
		{
			$maxexec = 10;
		}

		// Some servers report silly values, i.e. 30000, which Do Not Work™ :(
		if ($maxexec > 180)
		{
			$maxexec = 10;
		}

		// Sometimes memlimit comes with the M or K suffixes. Parse them.
		if (is_string($memlimit))
		{
			$memlimit = strtoupper(trim(str_replace(' ', '', $memlimit)));

			if (substr($memlimit, - 1) == 'K')
			{
				$memlimit = 1024 * substr($memlimit, 0, - 1);
			}
			elseif (substr($memlimit, - 1) == 'M')
			{
				$memlimit = 1024 * 1024 * substr($memlimit, 0, - 1);
			}
			elseif (substr($memlimit, - 1) == 'G')
			{
				$memlimit = 1024 * 1024 * 1024 * substr($memlimit, 0, - 1);
			}
		}

		// Unknown limit; suppose 16M
		if (!is_numeric($memlimit) || ($memlimit === 0))
		{
			$memlimit = 16777216;
		}

		// No limit; suppose 128M
		if ($memlimit === - 1)
		{
			$memlimit = 134217728;
		}

		// Get the current memory usage (or assume one if the metric is not available)
		if (function_exists('memory_get_usage'))
		{
			$usedram = memory_get_usage();
		}
		else
		{
			$usedram = 7340032; // Suppose 7M of RAM usage if the metric isn't available;
		}

		// If we have less than 12M of RAM left, we have to limit ourselves to 6 seconds of
		// total execution time (emperical value!) to avoid deadly memory outages
		if (($memlimit - $usedram) < 12582912)
		{
			$maxexec = 5;
		}

		// If the requested delay is over the $maxexec limit (minus one second
		// for application initialization), return false
		if ($secondsDelay > ($maxexec - 1))
		{
			return false;
		}

		// And now, run the silly loop to simulate the CPU usage pattern during backup
		$start = microtime(true);
		$loop  = true;

		while ($loop)
		{
			// Waste some CPU power...
			for ($i = 1; $i < 1000; $i ++)
			{
				$j = exp(($i * $i / 123 * 864) >> 2);
			}

			// ... then sleep for a millisec
			usleep(1000);

			// Are we done yet?
			$end = microtime(true);

			if (($end - $start) >= $secondsDelay)
			{
				$loop = false;
			}
		}

		return true;
	}

	/**
	 * This method will analyze your database tables and try to figure out the optimal batch row count value so that its
	 * SELECT doesn't return excessive amounts of data. The only drawback is that it only accounts for the core tables,
	 * but that is usually a good metric.
	 *
	 * @return  void
	 */
	public function analyzeDatabase()
	{
		// Try to get the PHP memory limit
		if (function_exists('ini_get'))
		{
			$memlimit = ini_get("memory_limit");
		}
		else
		{
			$memlimit = 16777216;
		}

		if (!is_numeric($memlimit) || ($memlimit === 0))
		{
			$memlimit = 16777216; // Unknown limit; suppose 16M
		}

		if ($memlimit === - 1)
		{
			$memlimit = 134217728; // No limit; suppose 128M
		}

		// Get the current memory usage (or assume one if the metric is not available)
		if (function_exists('memory_get_usage'))
		{
			$usedram = memory_get_usage();
		}
		else
		{
			$usedram = 7340032; // Suppose 7M of RAM usage if the metric isn't available;
		}

		// How much RAM can I spare? It's the max memory minus the current memory usage and an extra
		// 5Mb to cater for Akeeba Engine's peak memory usage
		$max_mem_usage = $usedram + 5242880;
		$ram_allowance = $memlimit - $max_mem_usage;

		// If the RAM allowance is too low, assume 2Mb (emperical value)
		if ($ram_allowance < 2097152)
		{
			$ram_allowance = 2097152;
		}

		// If SHOW TABLE STATUS is not supported this is a safe-ish value.
		$rowCount = 100;

		// Get the table statistics
		$db = $this->container->db;

		if (strtolower(substr($db->name, 0, 5)) == 'mysql')
		{
			// The table analyzer only works with MySQL
			$db->setQuery("SHOW TABLE STATUS");

			try
			{
				$metrics = $db->loadAssocList();

				if (method_exists($db, 'getError') && $db->getError())
				{
					$metrics = null;
				}
			}
			catch (\Exception $exc)
			{
				$metrics = null;
			}

			// SHOW TABLE STATUS is supported.
			if (!is_null($metrics))
			{
				$rowCount = 1000; // Start with the default value

				if (!empty($metrics))
				{
					foreach ($metrics as $table)
					{
						// Get row count and average row length
						$rows    = $table['Rows'];
						$avg_len = $table['Avg_row_length'];

						// Calculate RAM usage with current settings
						$max_rows        = min($rows, $rowCount);
						$max_ram_current = $max_rows * $avg_len;

						if ($max_ram_current > $ram_allowance)
						{
							// Hm... over the allowance. Let's try to find a sweet spot.
							$max_rows = (int) ($ram_allowance / $avg_len);
							// Quantize to multiple of 10 rows
							$max_rows = 10 * floor($max_rows / 10);

							// Can't really go below 10 rows / batch
							if ($max_rows < 10)
							{
								$max_rows = 10;
							}

							// If the new setting is less than the current $rowCount, use the new setting
							if ($rowCount > $max_rows)
							{
								$rowCount = $max_rows;
							}
						}
					}
				}
			}
		}

		$profile_id = Platform::getInstance()->get_active_profile();
		$config     = Factory::getConfiguration();

		// Use the correct database dump engine
		$config->set('akeeba.advanced.dump_engine', 'reverse');

		if (strpos($db->name, 'mysql') !== false)
		{
			$config->set('akeeba.advanced.dump_engine', 'native');
		}

		// Save the row count per batch
		$config->set('engine.dump.common.batchsize', $rowCount);

		// Enable SQL file splitting - default is 512K unless the part_size is less than that!
		$splitsize = 524288;
		$partsize  = $config->get('engine.archiver.common.part_size', 0);

		if (($partsize < $splitsize) && !empty($partsize))
		{
			$splitsize = $partsize;
		}

		$config->set('engine.dump.common.splitsize', $splitsize);

		// Enable extended INSERTs
		$config->set('engine.dump.common.extended_inserts', '1');

		// Determine optimal packet size (must be at most two fifths of the split size and no more than 256K)
		$packet_size = (int) $splitsize * 0.4;

		if ($packet_size > 262144)
		{
			$packet_size = 262144;
		}

		$config->set('engine.dump.common.packet_size', $packet_size);

		// Enable the native dump engine
		$config->set('akeeba.advanced.dump_engine', 'native');

		Platform::getInstance()->save_configuration($profile_id);
	}

	/**
	 * Executes the action requested through AJAX
	 *
	 * @return  bool
	 */
	public function runAjax()
	{
		// Only allowed actions
		$allowedActions = [
			'ping', 'minexec', 'applyminexec', 'directories', 'database', 'maxexec', 'applymaxexec', 'partsize'
		];

		// Get the requested action from the model state
		$action = $this->getState('act');

		$result = false;

		if (in_array($action, $allowedActions) && method_exists($this, $action))
		{
			$result = call_user_func([$this, $action]);
		}

		return $result;
	}

	/**
	 * Pings the configuration wizard process and marks the current profile as configured
	 *
	 * @return  bool  TRUE, always
	 */
	private function ping()
	{
		// Get the profile ID
		$profile_id = Platform::getInstance()->get_active_profile();

		// Set the embedded installer to the default ANGIE installer
		$engineConfig = Factory::getConfiguration();
		$engineConfig->set('akeeba.advanced.embedded_installer', 'angie');

		// And mark this profile as already configured
		$engineConfig->set('akeeba.flag.confwiz', 1);

		Platform::getInstance()->save_configuration($profile_id);

		return true;
	}

	/**
	 * Try different values of minimum execution time
	 *
	 * @return  bool  TRUE, always
	 */
	private function minexec()
	{
		$seconds = $this->input->get('seconds', '0.5', 'float');

		if ($seconds < 1)
		{
			usleep($seconds * 1000000);
		}
		else
		{
			sleep($seconds);
		}

		return true;
	}

	/**
	 * Saves the AJAX preference and the minimum execution time
	 *
	 * @return  bool  TRUE, always
	 */
	private function applyminexec()
	{
		// Get the user parameters
		$minexec = $this->input->get('minexec', 2.0, 'float');

		// Save the settings
		$profile_id   = Platform::getInstance()->get_active_profile();
		$engineConfig = Factory::getConfiguration();
		$engineConfig->set('akeeba.tuning.min_exec_time', $minexec * 1000);
		Platform::getInstance()->save_configuration($profile_id);

		// Enforce the min exec time
		$timer = Factory::getTimer();
		$timer->enforce_min_exec_time(false);

		// Done!
		return true;
	}

	/**
	 * Try to make the directories writable or provide a set of writable directories
	 *
	 * @return  bool  TRUE if we coud fix the permissions of the directories
	 */
	private function directories()
	{
		$timer  = Factory::getTimer();
		$result = $this->autofixDirectories();
		$timer->enforce_min_exec_time(false);

		return $result;
	}

	/**
	 * Analyze the database and apply optimized database dump settings
	 *
	 * @return  bool  TRUE if we were successful
	 */
	private function database()
	{
		$timer = Factory::getTimer();
		$this->analyzeDatabase();
		$timer->enforce_min_exec_time(false);

		return true;
	}

	/**
	 * Try to apply a specific maximum execution time setting
	 *
	 * @return  bool
	 */
	private function maxexec()
	{
		$seconds = $this->input->get('seconds', 30, 'int');
		$timer   = Factory::getTimer();
		$result  = $this->doNothing($seconds);
		$timer->enforce_min_exec_time(false);

		return $result;
	}

	/**
	 * Save a specific maximum execution time preference to the database
	 *
	 * @return  bool
	 */
	private function applymaxexec()
	{
		// Get the user parameters
		$maxexec = $this->input->get('seconds', 2, 'int');

		// Save the settings
		$timer      = Factory::getTimer();
		$profile_id = Platform::getInstance()->get_active_profile();
		$config     = Factory::getConfiguration();
		$config->set('akeeba.tuning.max_exec_time', $maxexec);
		$config->set('akeeba.tuning.run_time_bias', '75');
		$config->set('akeeba.advanced.scan_engine', 'smart');
		$config->set('akeeba.advanced.archiver_engine', 'jpa');
		Platform::getInstance()->save_configuration($profile_id);

		// Enforce the min exec time
		$timer->enforce_min_exec_time(false);

		// Done!
		return true;
	}

	/**
	 * Creates a dummy file of a given size. Remember to give the filesize query parameter in bytes!
	 *
	 * @return  bool
	 */
	public function partsize()
	{
		$timer  = Factory::getTimer();
		$blocks = $this->input->get('blocks', 1, 'int');

		$result = $this->createTempFile($blocks);

		if ($result)
		{
			// Save the setting
			if ($blocks > 200)
			{
				$blocks = 16383; // Over 25Mb = 2Gb minus 128Kb limit (safe setting for PHP not running on 64-bit Linux)
			}

			$profile_id = Platform::getInstance()->get_active_profile();
			$config     = Factory::getConfiguration();
			$config->set('engine.archiver.common.part_size', $blocks * 128 * 1024);
			Platform::getInstance()->save_configuration($profile_id);
		}

		// Enforce the min exec time
		$timer->enforce_min_exec_time(false);

		return $result;
	}
}
