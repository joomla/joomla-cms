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
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

require_once( 'includes/joomla.php' );
@include_once ('language/'.$mosConfig_lang.'.php');

global $option, $database;
global $mosConfig_live_site;

// gets template for page
$cur_template = @$mainframe->getTemplate();
if ( !$cur_template ) {
	$cur_template = 'rhuk_solarflare_ii';
}

// HTML Output
header(' Content-Type: text/htm; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<style>
table.moswarning {
	font-size: 200%;
	background-color: #c00;
	color: #fff;
	border-bottom: 2px solid #600
}

table.moswarning h2 {
	padding: 0;
	margin: 0;
	text-align: center;
	font-family: Arial, Helvetica, sans-serif;
}

</style>
<title><?php echo $mosConfig_sitename; ?> - Offline</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"" />
<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/templates/<?php echo $cur_template;?>/css/template_css.css" type="text/css" />
</head>
<body style="margin: 0px; padding: 0px;">

<table width="100%" align="center" class="moswarning">
<?php
if ( $mosConfig_offline == 1 ) {
	?>
	<tr>
		<td>
			<h2>
			<?php
			echo $mosConfig_sitename;
			echo ' - ';
			echo $mosConfig_offline_message;
			?>
			</h2>
		</td>
	</tr>
	<?php
} else if ( @$mosSystemError ){
	?>
	<tr>
		<td>
			<h2>
			<?php echo $mosConfig_error_message; ?>
			</h2>
			<?php echo $mosSystemError; ?>
		</td>
	</tr>
	<?php
} else {
	?>
	<tr>
		<td>
			<h2>
			<?php echo $_LANG->_( 'WARNINSTALL' ); ?>
			</h2>
		</td>
	</tr>
	<?php
}
?>
</table>

</body>
</html>
