<?php
/**
* @version $Id: mod_logoutbutton.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $my, $acl;
global $_LANG;

$link_user 		= '#';
$link_logout	= '#';
$title_user		= $_LANG->_( 'Logged in User' );
$title_logout	= '';

if ( !$mainframe->get('disableMenu', false) ) {
	if ( $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype ) ) {
		$link_user 	= 'index2.php?option=com_users&amp;task=editA&amp;id='. $my->id;
		$title_user	= $_LANG->_( 'Edit User Information' );
	}
	$link_logout	= 'index2.php?option=logout';
	$title_logout	= $_LANG->_( 'Logout' );
}
?>
<span style="padding-left: 15px;">
<?php echo $_LANG->_( 'User' ); ?>:
</span>
<strong>
<a href="<?php echo $link_user; ?>" title="<?php echo $title_user; ?>" style="text-decoration: none;">
<?php echo $my->username;?></a>
</strong>
<a href="<?php echo $link_logout; ?>" class="logoutButton" title="<?php echo $title_logout; ?>">
<?php echo $_LANG->_( 'Logout' ); ?></a>