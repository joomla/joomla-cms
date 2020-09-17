<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Timer;

defined('_JEXEC') || die;

/**
 * Timeout prevention timer
 */
class Timer
{
	/**
	 * Maximum execution time allowance per step
	 *
	 * @var  integer
	 */
	private $max_exec_time = null;

	/**
	 * Timestamp of execution start
	 *
	 * @var  integer
	 */
	private $start_time = null;

	/**
	 * Public constructor, creates the timer object and calculates the execution
	 * time limits.
	 *
	 * @param   integer  $max_exec_time  Maximum execution time, in seconds
	 * @param   integer  $runtime_bias   Runtime bias factor, as percent points of the max execution time
	 *
	 * @return  Timer
	 */
	public function __construct($max_exec_time = 5, $runtime_bias = 75)
	{
		// Initialize start time
		$this->start_time = microtime(true);

		$this->max_exec_time = $max_exec_time * $runtime_bias / 100;
	}

	/**
	 * Wake-up function to reset internal timer when we get unserialized
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		// Re-initialize start time on wake-up
		$this->start_time = microtime(true);
	}

	/**
	 * Gets the number of seconds left, before we hit the "must break" threshold
	 *
	 * @return  float
	 */
	public function getTimeLeft()
	{
		return $this->max_exec_time - $this->getRunningTime();
	}

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively
	 * how long this step is running
	 *
	 * @return  float
	 */
	public function getRunningTime()
	{
		return microtime(true) - $this->start_time;
	}

	/**
	 * Reset the timer
	 *
	 * @return  void
	 */
	public function resetTime()
	{
		$this->start_time = microtime(true);
	}
}
