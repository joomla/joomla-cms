<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util\Log;

defined('AKEEBAENGINE') || die();

interface WarningsLoggerInterface
{
	/**
	 * Returns an array with all warnings logged since the last time warnings were reset. The maximum number of warnings
	 * returned is controlled by setWarningsQueueSize().
	 *
	 * @return array
	 */
	public function getWarnings();

	/**
	 * Resets the warnings queue.
	 *
	 * @return void
	 */
	public function resetWarnings();

	/**
	 * A combination of getWarnings() and resetWarnings(). Returns the warnings and immediately resets the warnings
	 * queue.
	 *
	 * @return array
	 */
	public function getAndResetWarnings();

	/**
	 * Set the warnings queue size. A size of 0 means "no limit".
	 *
	 * @param   int  $queueSize  The size of the warnings queue (in number of warnings items)
	 *
	 * @return void
	 */
	public function setWarningsQueueSize($queueSize = 0);

	/**
	 * Returns the warnings queue size.
	 *
	 * @return int
	 */
	public function getWarningsQueueSize();
}
