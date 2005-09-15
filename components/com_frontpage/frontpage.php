<?php
/**
* @version $Id: frontpage.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// load the content language file
$_LANG->load( 'com_content', 0 );

// definition that we have to run the frontpage
if( $task == '' ) {
	$task = 'frontpage';
}
// code handling has been shifted into content.php
require_once( $mosConfig_absolute_path .'/components/com_content/content.php' );
?>