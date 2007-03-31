<?php
/**
* @version		$Id:phputf8env.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Compatibility
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * PHP mbstring and iconv local configuration
 *
 * @package		Joomla.Framework
 * @subpackage	Compatibility
 * @since		1.5
 */
// check if mbstring extension is loaded and attempt to load it if not present except for windows
if (extension_loaded('mbstring') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('mbstring.so')))) {
	//Make sure to surpress the output in case ini_set is disabled
	@ini_set('mbstring.internal_encoding', 'UTF-8');
	@ini_set('mbstring.http_input', 'UTF-8');
	@ini_set('mbstring.http_output', 'UTF-8');
}

// same for iconv
if (function_exists('iconv') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('iconv.so')))) {
   	// these are settings that can be set inside code
	iconv_set_encoding("internal_encoding", "UTF-8");
	iconv_set_encoding("input_encoding", "UTF-8");
	iconv_set_encoding("output_encoding", "UTF-8");
}