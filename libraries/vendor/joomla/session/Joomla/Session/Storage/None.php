<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * Default PHP configured session handler for Joomla!
 *
 * @see    http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since  1.0
 * @deprecated  The joomla/session package is deprecated
 */
class None extends Storage
{
	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register()
	{
	}
}
