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

if (file_exists( '../configuration.php' ) && filesize( '../configuration.php' ) > 10) {
	header( 'Location: ../index.php' );
	exit();
}
/** Include common.php */
include_once( 'common.php' );
function writableCell( $folder ) {
	echo "<tr>";
	echo "<td class=\"item\">" . $folder . "/</td>";
	echo "<td align=\"left\">";
	echo is_writable( "../$folder" ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>' . "</td>";
	echo "</tr>";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Joomla - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../../images/favicon.ico" />
<link rel="stylesheet" href="install.css" type="text/css" />
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="joomla"><img src="header_install.png" alt="Joomla Installation" /></div>
	</div>
</div>
<div id="ctr" align="center">
	<form action="install1.php" method="post" name="adminForm" id="adminForm">
	<div class="install">
	<div id="stepbar">
		<div class="step-off">pre-installation check</div>
		<div class="step-on">license</div>
		<div class="step-off">step 1</div>
		<div class="step-off">step 2</div>
		<div class="step-off">step 3</div>
		<div class="step-off">step 4</div>
	</div>
	<div id="right">
		<div id="step">license</div>
		<div class="far-right">
		<input class="button" type="submit" name="next" value="Next &gt;&gt;"/>
		</div>
		<div class="clr"></div>
		<h1>GNU/GPL License:</h1>
		<div class="licensetext">
				<a href="http://www.joomla.org">Joomla </a> is Free Software released under the GNU/GPL License.
		</div>
		<div class="clr"></div>
		<div class="license-form">
			<div class="form-block" style="padding: 0px;">
				<iframe src="gpl.html" class="license" frameborder="0" scrolling="auto"></iframe>
			</div>
		</div>
		<div class="clr"></div>
		<div class="clr"></div>
		</div>
		<div id="break"></div>
	<div class="clr"></div>
	<div class="clr"></div>
	</div>
	</form>
</div>
<div class="ctr">
	<a href="http://www.joomla.org" target="_blank">Joomla!</a> is Free Software released under the GNU/GPL License.
</div>
</body>
</html>