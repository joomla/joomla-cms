<?php
/**
* @version $Id: mod_unread.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$query = "SELECT COUNT(*)"
. "\n FROM #__messages"
. "\n WHERE state = '0'"
. "\n AND user_id_to = '$my->id'"
;
$database->setQuery( $query );
$unread = $database->loadResult();

$txt 	= $_LANG->_( 'Administration Messages' );
$link	= 'index2.php?option=com_messages';

if ( $unread ) {
	$style = 'color: red; text-decoration: none;  font-weight: bold';
	$image = 'images/mail.png';
} else {
	$style = 'color: black; text-decoration: none;';
	$image = 'images/nomail.png';
}

if ( $mainframe->get('disableMenu', false) ) {
	$link = '#';
}
?>
<a href="<?php echo $link; ?>" style="<?php echo $style; ?>">
<?php echo $unread; ?>
<img src="<?php echo $image; ?>" align="middle" border="0" alt="<?php echo $txt; ?>" title="<?php echo $txt; ?>" />
</a>