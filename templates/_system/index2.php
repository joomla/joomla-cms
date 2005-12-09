<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// start basic HTML
if ( $no_html == 0 ) {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<?php echo $mainframe->getHead(); ?>
	<link rel="stylesheet" href="<?php echo JURL_SITE;?>/templates/<?php echo $cur_template;?>/css/template_css.css" type="text/css" />
	<link rel="shortcut icon" href="<?php echo JURL_SITE; ?>/images/favicon.ico" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	</head>
	<body class="contentpane">
	<?php mosMainBody(); ?>
	</body>
	</html>
	<?php
}

if($no_html == 1 ) {
	mosMainBody();
}

?>