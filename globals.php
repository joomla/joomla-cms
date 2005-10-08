<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

if (!ini_get('register_globals')) {
	while (list( $key, $value ) = each( $_FILES )) $GLOBALS[$key] = $value;
	while (list( $key, $value ) = each( $_ENV )) $GLOBALS[$key] = $value;
	while (list( $key, $value ) = each( $_GET )) $GLOBALS[$key] = $value;
	while (list( $key, $value ) = each( $_POST )) $GLOBALS[$key] = $value;
	while (list( $key, $value ) = each( $_COOKIE )) $GLOBALS[$key] = $value;
	while (list( $key, $value ) = each( $_SERVER )) $GLOBALS[$key] = $value;	
	
	if (isset($_SESSION)) {
		while (list( $key, $value ) = @each( $_SESSION )) $GLOBALS[$key] = $value;
	}	
	
	foreach ($_FILES as $key => $value){
		$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
		foreach ($value as $ext => $value2){
			$key2 = $key . '_' . $ext;
			$GLOBALS[$key2] = $value2;
		}
	}
}
?>