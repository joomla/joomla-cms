<?php
/**
 * PHP 4.1.x Compatibility functions
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
				$array[JString::strtoupper( $k )] = $v;
			} else {
				$array[JString::strtolower( $k )] = $v;
			}
		}
		return $array;
	}
}
?>