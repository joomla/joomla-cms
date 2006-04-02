<?php
/**
* @version $Id: legacy.php 1525 2005-12-21 21:08:29Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * PHP 4.1.x Compatibility functions
 *
 * @package		Joomla.Framework
 * @subpackage	Compatibility
 * @since		1.0
 */


if (!function_exists( 'array_change_key_case' )) {
	if (!defined('CASE_LOWER')) {
		define('CASE_LOWER', 0);
	}
	if (!defined('CASE_UPPER')) {
		define('CASE_UPPER', 1);
	}
	function array_change_key_case( $input, $case=CASE_LOWER ) {
		if (!is_array( $input )) {
			return false;
		}
		$array = array();
		foreach ($input as $k=>$v) {
			if ($case) {
				$array[strtoupper( $k )] = $v;
			} else {
				$array[strtolower( $k )] = $v;
			}
		}
		return $array;
	}
}
?>