<?php

/**
* @version $Id: joomla.factory.php 719 2005-10-28 14:44:21Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * The Joomla! Factory class
 * @package Joomla
 * @since 1.1
 */
class JFactory {
	/**
	* Load language files
	* The function will load the common language file of the system and the
	* special files for the actual component.
	* The module related files will be loaded automatically
	*
	* @subpackage Language
	* @param string		actual component which files should be loaded
	* @param boolean	admin languages to be loaded?
	*/
	function &getLanguage( $option=null, $isAdmin=false ) {
		global $mosConfig_absolute_path, $mainframe;
		global $mosConfig_lang, $my;

		require_once $mosConfig_absolute_path .'/libraries/joomla/language.php';

		$mosConfig_admin_path = $mosConfig_absolute_path .'/administrator';
		$path = $mosConfig_absolute_path . '/language/';
		
		$lang = $mainframe->getUserState( 'lang' );
		
		if ($lang == '' && $my && isset( $my->params )) {

			// if admin && special lang?
			if( $mainframe && $mainframe->isAdmin() ) {
				$lang = $my->params->get( 'admin_language', $lang );
			}
		}
		
		// loads english language file by default
		if ($lang == '0' || $lang == '') {
			$lang = $mosConfig_lang;
		}

		// load the site language file (the old way - to be deprecated)
		$file = $path . $lang .'.php';
		if (file_exists( $file )) {
			require_once( $path . $lang .'.php' );
		} else {
			$file = $path .'english.php';
			if (file_exists( $file )) {
				require_once( $file );
			}
		}

		$_LANG = new mosLanguage( $lang );
		$_LANG->loadAll( $option, 0 );
		if ($isAdmin) {
			$_LANG->loadAll( $option, 1 );
		}

		// make sure the locale setting is correct
		setlocale( LC_ALL, $_LANG->locale() );

		// In case of frontend modify the config value in order to keep backward compatiblitity
		if( $mainframe && !$mainframe->isAdmin() ) {
			$mosConfig_lang = $lang;
		}

		return $_LANG;
	}
}
?>