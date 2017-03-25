<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * Default PHP configured session handler for Joomla!
 *
 * @see         http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since       1.0
 * @deprecated  2.0  The Storage class chain will be removed
 */
class None extends Storage
{
	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public function register()
	{
	}
}
