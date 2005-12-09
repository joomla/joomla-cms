<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$steps = array(
	'lang' => 'off',
	'preinstall' => 'off',
	'license' => 'off',
	'dbconfig' => 'off',
	'dbcollation' => 'off',
	'ftpconfig' => 'off',
	'mainconfig' => 'off',
	'finish' => 'off'
);

/**
* Utility function to return a value from a named array or a specified default
*/
define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
function mosGetParam( &$arr, $name, $def=null, $mask=0 ) {
	$return = null;
	if (isset( $arr[$name] )) {
		if (is_string( $arr[$name] )) {
			if (!($mask&_MOS_NOTRIM)) {
				$arr[$name] = trim( $arr[$name] );
			}
			if (!($mask&_MOS_ALLOWHTML)) {
				$arr[$name] = strip_tags( $arr[$name] );
			}
			if (!get_magic_quotes_gpc()) {
				$arr[$name] = addslashes( $arr[$name] );
			}
		}
		return $arr[$name];
	} else {
		return $def;
	}
}

function mosMakePassword($length) {
	$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$len = strlen($salt);
	$makepass='';
	mt_srand(10000000*(double)microtime());
	for ($i = 0; $i < $length; $i++)
	$makepass .= $salt[mt_rand(0,$len - 1)];
	return $makepass;
}


function get_php_setting($val) {
	$r =  (ini_get($val) == '1' ? 1 : 0);
	return $r ? 'ON' : 'OFF';
}

/**
 * Tries to detect the language
 */
function detectLanguage() {

	$vars = mosGetParam( $_REQUEST, 'vars', array() );

	$client_lang = '';
	if ($_SERVER['HTTP_ACCEPT_LANGUAGE'] != '') {
		$languages = JLanguageHelper::createLanguageList( '', JPATH_INSTALLATION );
		$active_lang = array();

		foreach ($languages as $language) {
			$LANG = new JLanguage($language['value']);
			$LANG->load('');
			$active_lang[$LANG->getTag()] = $language['value'];
		}

		$browserLang = explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );

		foreach ($browserLang as $lang) {
			$shortLang = substr( $lang, 0, 2 );
			if (isset( $active_lang[$lang] )) {
				$client_lang = $active_lang[$lang];
				break;
			}
			if (isset( $active_lang[$shortLang] )) {
				$client_lang = $active_lang[$shortLang];
				break;
			}
		}
	}

	if ($client_lang=='') {
		$client_lang = 'eng_GB';
	}

	$lang = mosGetParam( $vars, 'lang', $client_lang );
	return $lang;
}

/**
 * Format a backtrace error
 * @since 1.1
 */
function mosBackTrace() {
	if (function_exists( 'debug_backtrace' )) {
		echo '<div align="left">';
		foreach( debug_backtrace() as $back) {
			if (@$back['file']) {
				echo '<br />' . str_replace( JPATH_ROOT, '', $back['file'] ) . ':' . $back['line'];
			}
		}
		echo '</div>';
	}
}
?>