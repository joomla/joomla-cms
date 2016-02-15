<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('FOF_INCLUDED') or die;

/**
 * An execution timer monitor class
 */
class FOFUtilsTimer
{
	/** @var float Maximum execution time allowance */
	private $max_exec_time = null;

	/** @var float Timestamp of execution start */
	private $start_time = null;

	/**
	 * Public constructor, creates the timer object and calculates the execution
	 * time limits.
	 *
	 * @param float $max_exec_time Maximum execution time allowance
	 * @param float $runtime_bias  Execution time bias (expressed as % of $max_exec_time)
	 */
	public function __construct($max_exec_time = 5.0, $runtime_bias = 75.0)
	{
		// Initialize start time
		$this->start_time = $this->microtime_float();

		$this->max_exec_time = $max_exec_time * $runtime_bias / 100.0;
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
	 * Gets the number of seconds left, before we hit the "must break" threshold. Negative
	 * values mean that we have already crossed that threshold.
	 *
	 * @return  float
	 */
	public function getTimeLeft()
	{
		return $this->max_exec_time - $this->getRunningTime();
	}

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively
	 * how long we are running
	 *
	 * @return  float
	 */
	public function getRunningTime()
	{
		return $this->microtime_float() - $this->start_time;
	}

	/**
	 * Returns the current timestamp in decimal seconds
	 *
	 * @return  float
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Reset the timer
	 */
	public function resetTime()
	{
		$this->start_time = $this->microtime_float();
	}

}