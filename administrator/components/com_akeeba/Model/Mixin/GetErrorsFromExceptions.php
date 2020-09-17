<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model\Mixin;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Exception;
use Throwable;

trait GetErrorsFromExceptions
{
	/**
	 * Retrieve the messages from nested exceptions into an array. It will optionally add the trace as the last element
	 * of the array if debug mode (JDEBUG or AKEEBADEBUG) is enabled and $includeTraceInDebug is true.
	 *
	 * @param   Exception|Throwable  $exception            The Exception or Throwable to log
	 *
	 * @param   bool                 $includeTraceInDebug  Include the trace when debug mode is enabled
	 *
	 * @return  array
	 */
	public function getErrorsFromExceptions($exception, $includeTraceInDebug = true)
	{
		$ret = [
			$exception->getMessage(),
		];

		$previous = $exception->getPrevious();

		if (!is_null($previous))
		{
			$ret = array_merge($ret, $this->getErrorsFromExceptions($previous, false));
		}

		if ($includeTraceInDebug && ((defined('JDEBUG') && JDEBUG) || (defined('AKEEBADEBUG') && AKEEBADEBUG)))
		{
			$ret[] = $exception->getTraceAsString();
		}

		return $ret;
	}

}
