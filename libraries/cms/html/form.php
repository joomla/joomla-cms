<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for form elements
 *
 * @since  1.5
 */
abstract class JHtmlForm
{
	/**
	 * Displays a hidden token field to reduce the risk of CSRF exploits
	 *
	 * Use in conjunction with JSession::checkToken()
	 *
	 * @return  string  A hidden input field with a token
	 *
	 * @see     JSession::checkToken()
	 * @since   1.5
	 */
	public static function token()
	{
		return '<input type="hidden" name="' . JSession::getFormToken() . '" value="1" />';
	}
}
