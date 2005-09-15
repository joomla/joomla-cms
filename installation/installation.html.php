<?php
/**
* @version $Id: installation.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Installation
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Mambo
 * @subpackage Installation
 */
class installationScreens {
	/**
	 * Static method to create the template object
	 * @return patTemplate
	 */
	function &createTemplate() {
		global $mosConfig_absolute_path, $_LANG;

		require_once( $mosConfig_absolute_path . '/includes/patTemplate/patTemplate.php' );

		$tmpl = new patTemplate;
		$tmpl->setNamespace( 'mos' );

		// load the wrapper and common templates
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
		$tmpl->readTemplatesFromFile( 'page.html' );

		if ($_LANG->iso()) {
			$tmpl->addGlobalVar( 'lang_iso', 		$_LANG->iso() );
			$tmpl->addGlobalVar( 'lang_charset', 	'charset=' . $_LANG->iso() );
		} else {
			// TODO: Try and determine the charset from the browser
			$tmpl->addGlobalVar( 'lang_iso', 		'iso-8859-1' );
			$tmpl->addGlobalVar( 'lang_charset', 	'charset=iso-8859-1' );
		}

		if ($_LANG->rtl()) {
			$tmpl->addGlobalVar( 'installcss', 'install_rtl.css' );
		} else {
			$tmpl->addGlobalVar( 'installcss', 'install.css' );
		}

		return $tmpl;
	}

	/**
	 * Shows an error message and back link
	 * @param array Vars to add to the error form
	 * @param mixed A string or array of messages
	 * @param string The name of the step to go back to
	 * @param string An extra message to display in a text area
	 */
	function error( &$vars, $msg, $back, $xmsg='' ) {
		global $steps;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'error.html' );

		$tmpl->addVars( 'stepbar', $steps, 		'step_' );
		$tmpl->addVar( 'messages', 'message', 	$msg );

		if ($xmsg) {
			$tmpl->addVar( 'xmessages', 'xmessage', $xmsg );
		}

		$tmpl->addVar( 'body', 'back', $back );
		$tmpl->addVars( 'body', $vars, 'var_' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function chooseLanguage( &$lists ) {
		global $steps;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'language.html' );

		$steps['lang'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addRows( 'lang-options', $lists['langs'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function preInstall( $vars, &$lists ) {
		global $steps, $_VERSION;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'preinstall.html' );

		$steps['preinstall'] = 'on';

		$tmpl->addVars( 'stepbar', 	$steps, 	'step_' );
		$tmpl->addVar( 'body', 		'version', 	$_VERSION->getLongVersion() );
		$tmpl->addVars( 'body', 	$vars, 		'var_' );

		$tmpl->addRows( 'php-options', 	$lists['phpOptions'] );
		$tmpl->addRows( 'php-settings', $lists['phpSettings'] );
		$tmpl->addRows( 'folder-perms', $lists['folderPerms'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function license( &$vars ) {
		global $steps;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'license.html' );

		$steps['license'] = 'on';

		$tmpl->addVars( 'stepbar', 	$steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 	'var_' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function dbConfig( &$vars, &$lists ) {
		global $steps;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'dbconfig.html' );

		$steps['dbconfig'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );

		$tmpl->addRows( 'dbtype-options', $lists['dbTypes'] );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function mainConfig( &$vars ) {
		global $steps;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'mainconfig.html' );

		$steps['mainconfig'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );

		$tmpl->addVar( 'body', 'filemode', 	'0' );
		$tmpl->addVar( 'body', 'dirmode', 	'0' );

		$tmpl->displayParsedTemplate( 'form' );
	}

	/**
	 * The finish page for the installer
	 * @param array An array of lists
	 * @param string The configuration file if it could not be saved
	 */
	function finish( &$vars, $buffer ) {
		global $steps;

		$tmpl =& installationScreens::createTemplate();
		$tmpl->setAttribute( 'body', 'src', 'finish.html' );

		$steps['finish'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );

		if ($buffer) {
			$tmpl->addVar( 'configuration-error', 'buffer', $buffer );
		}

		$tmpl->displayParsedTemplate( 'form' );
	}
}
?>