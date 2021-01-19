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

/**
 * Timer class
 */
class Timer
{

	/** @var int Maximum execution time allowance per step */
	private $max_exec_time = null;

	/** @var int Timestamp of execution start */
	private $start_time = null;

	/**
	 * Public constructor, creates the timer object and calculates the execution time limits
	 *
	 * @return  void
	 */
	public function __construct()
	{
		// Initialize start time
		$this->start_time = $this->microtime_float();

		// Get configured max time per step and bias
		$configuration        = Factory::getConfiguration();
		$config_max_exec_time = $configuration->get('akeeba.tuning.max_exec_time', 14);
		$bias                 = $configuration->get('akeeba.tuning.run_time_bias', 75) / 100;

		$this->max_exec_time = $config_max_exec_time * $bias;
	}

	/**
	 * Wake-up function to reset internal timer when we get unserialized
	 */
	public function __wakeup()
	{
		// Re-initialize start time on wake-up
		$this->start_time = $this->microtime_float();
	}

	/**
	 * Gets the number of seconds left, before we hit the "must break" threshold
	 *
	 * @return float
	 */
	public function getTimeLeft()
	{
		return $this->max_exec_time - $this->getRunningTime();
	}

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively how
	 * long Akeeba Engine has been processing data
	 *
	 * @return float
	 */
	public function getRunningTime()
	{
		return $this->microtime_float() - $this->start_time;
	}

	/**
	 * Enforce the minimum execution time
	 *
	 * @param   bool  $log              Should I log what I'm doing? Default is true.
	 * @param   bool  $serverSideSleep  Should I sleep on the server side? If false we return the amount of time to wait in msec
	 *
	 * @return  int Wait time to reach min_execution_time in msec
	 */
	public function enforce_min_exec_time($log = true, $serverSideSleep = true)
	{
		// Try to get a sane value for PHP's maximum_execution_time INI parameter
		if (@function_exists('ini_get'))
		{
			$php_max_exec = @ini_get("maximum_execution_time");
		}
		else
		{
			$php_max_exec = 10;
		}
		if (($php_max_exec == "") || ($php_max_exec == 0))
		{
			$php_max_exec = 10;
		}
		// Decrease $php_max_exec time by 500 msec we need (approx.) to tear down
		// the application, as well as another 500msec added for rounding
		// error purposes. Also make sure this is never gonna be less than 0.
		$php_max_exec = max($php_max_exec * 1000 - 1000, 0);

		// Get the "minimum execution time per step" Akeeba Backup configuration variable
		$configuration = Factory::getConfiguration();
		$minexectime   = $configuration->get('akeeba.tuning.min_exec_time', 0);
		if (!is_numeric($minexectime))
		{
			$minexectime = 0;
		}

		// Make sure we are not over PHP's time limit!
		if ($minexectime > $php_max_exec)
		{
			$minexectime = $php_max_exec;
		}

		// Get current running time
		$elapsed_time = $this->getRunningTime() * 1000;

		$clientSideSleep = 0;

		// Only run a sleep delay if we haven't reached the minexectime execution time
		if (($minexectime > $elapsed_time) && ($elapsed_time > 0))
		{
			$sleep_msec = $minexectime - $elapsed_time;

			if (!$serverSideSleep)
			{
				Factory::getLog()->debug("Asking client to sleep for $sleep_msec msec");
				$clientSideSleep = $sleep_msec;
			}
			elseif (function_exists('usleep'))
			{
				if ($log)
				{
					Factory::getLog()->debug("Sleeping for $sleep_msec msec, using usleep()");
				}
				usleep(1000 * $sleep_msec);
			}
			elseif (function_exists('time_nanosleep'))
			{
				if ($log)
				{
					Factory::getLog()->debug("Sleeping for $sleep_msec msec, using time_nanosleep()");
				}
				$sleep_sec  = floor($sleep_msec / 1000);
				$sleep_nsec = 1000000 * ($sleep_msec - ($sleep_sec * 1000));
				time_nanosleep($sleep_sec, $sleep_nsec);
			}
			elseif (function_exists('time_sleep_until'))
			{
				if ($log)
				{
					Factory::getLog()->debug("Sleeping for $sleep_msec msec, using time_sleep_until()");
				}
				$until_timestamp = time() + $sleep_msec / 1000;
				time_sleep_until($until_timestamp);
			}
			elseif (function_exists('sleep'))
			{
				$sleep_sec = ceil($sleep_msec / 1000);
				if ($log)
				{
					Factory::getLog()->debug("Sleeping for $sleep_sec seconds, using sleep()");
				}
				sleep($sleep_sec);
			}
		}
		elseif ($elapsed_time > 0)
		{
			// No sleep required, even if user configured us to be able to do so.
			if ($log)
			{
				Factory::getLog()->debug("No need to sleep; execution time: $elapsed_time msec; min. exec. time: $minexectime msec");
			}
		}

		return $clientSideSleep;
	}

	/**
	 * Reset the timer. It should only be used in CLI mode!
	 */
	public function resetTime()
	{
		$this->start_time = $this->microtime_float();
	}

	/**
	 * Returns the current timestamp in decimal seconds
	 */
	protected function microtime_float()
	{
		[$usec, $sec] = explode(" ", microtime());

		return ((float) $usec + (float) $sec);
	}
}
