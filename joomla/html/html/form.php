<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class for form elements
 *
 * @static
 * @package		Joomla.Framework
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
	 * @since	1.5
	 */
	public static function token()
	{
		return '<input type="hidden" name="'.JUtility::getToken().'" value="1" />';
	}
}