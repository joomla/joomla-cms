<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for form elements
 *
 * @static
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @version		1.5
 */
abstract class JHtmlForm
{
	/**
	 * Displays a hidden token field to reduce the risk of CSRF exploits
	 *
	 * Use in conjuction with JRequest::checkToken
	 *
	 * @static
	 * @return	void
	 * @since	11.1
	 */
	public static function token()
	{
		return '<input type="hidden" name="'.JUtility::getToken().'" value="1" />';
	}
}