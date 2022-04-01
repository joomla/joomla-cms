<?php
/**
 * Part of the Joomla Framework Filesystem Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filesystem\Exception;

use Joomla\Filesystem\Path;

/**
 * Exception class for handling errors in the Filesystem package
 *
 * @since   1.2.0
 * @change  1.6.2  If the message containes a full path, the root path (JPATH_ROOT) is removed from it
 *          to avoid any full path disclosure. Before 1.6.2, the path was propagated as provided.
 */
class FilesystemException extends \RuntimeException
{
	/**
	 * Constructor.
	 *
	 * @param   string           $message   The message
	 * @param   integer          $code      The code
	 * @param   \Throwable|null  $previous  A previous exception
	 */
	public function __construct($message = "", $code = 0, \Throwable $previous = null)
	{
		parent::__construct(
			Path::removeRoot($message),
			$code,
			$previous
		);
	}
}
