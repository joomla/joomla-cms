<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google authentication class abstract
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
abstract class JGoogleAuth
{

	/**
	 * Abstract method to authenticate to Google
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1234
	 */
	abstract public function auth();

	/**
	 * Abstract method to retrieve data from Google
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 */
	abstract public function query();
}
