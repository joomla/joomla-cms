<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Interface for a CMS controller needed for dispatching
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
 */
interface JControllerCmsInterface extends JController
{
	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 * @since   3.4
	 */
	public function redirect();
}
