<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
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

// ensure user has access to this function
if ( !$acl->acl_check( 'administration', 'install', 'users', $my->usertype, $element . 's', 'all' ) ) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

$backlink = '<a href="index2.php?option=com_languages">'. $_LANG->_( 'Back to Language Manager' ) .'</a>';
HTML_installer::showInstallForm( $_LANG->_( 'Install new Language - Site' ), $option, 'language', '', dirname(__FILE__), $backlink );
?>
<table class="content">
<?php
writableCell( 'media' );
writableCell( 'language' );
?>
</table>