<?php
/**
* @version $Id: registration.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Users
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
 * @subpackage Registration
 */
class registrationScreens_front {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml='', $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );

		$directory = mosComponentDirectory( $bodyHtml, dirname( __FILE__ ) );
		$tmpl->setRoot( $directory );

		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	function lostPass() {
		global $mainframe;

		$params = new mosParameters( '' );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

		$tmpl =& registrationScreens_front::createTemplate( 'lostpass.html' );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}

	function register() {
		global $mosConfig_useractivation, $mosConfig_password_length, $mosConfig_username_length;
		global $mainframe;
		global $_LANG;

		$txt 		= sprintf( $_LANG->_('VALID_AZ09'), $_LANG->_( 'PROMPT_UNAME' ), $mosConfig_username_length - 1 );
		$tip_user 	= mosToolTip( $txt, $_LANG->_( 'REGISTER_UNAME' ) );

		$txt 		= sprintf( $_LANG->_('VALID_AZ09'), $_LANG->_( 'REGISTER_PASS' ), $mosConfig_password_length - 1 );
		$tip_pass 	= mosToolTip( $txt, $_LANG->_( 'REGISTER_PASS ' ));

		$params = new mosParameters( '' );
		$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );

		$tmpl =& registrationScreens_front::createTemplate( 'register.html' );

		$tmpl->addVar( 'body', 'tip_user',			$tip_user );
		$tmpl->addVar( 'body', 'tip_pass',			$tip_pass );

		$tmpl->addVar( 'body', 'useractivation',	$mosConfig_useractivation );
		$tmpl->addVar( 'body', 'length_name',		$mosConfig_username_length );
		$tmpl->addVar( 'body', 'length_pass',		$mosConfig_password_length );
		$tmpl->addVar( 'body', 'length_nameX',		$mosConfig_username_length - 1 );
		$tmpl->addVar( 'body', 'length_passX',		$mosConfig_password_length - 1 );

		$tmpl->addObject( 'body', $params->toObject(), 'p_' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>