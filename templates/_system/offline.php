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

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $mosConfig_sitename; ?> - Offline</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"" />
	<link rel="stylesheet" href="<?php echo JURL_SITE; ?>/templates/_system/css/offline.css" type="text/css" />
	<link rel="shortcut icon" href="<?php echo JURL_SITE; ?>/images/favicon.ico" />
</head>
<body>
	<p>&nbsp;</p>
	<table width="550" align="center" class="outline">
	<tr>
		<td width="60%" height="50" align="center">
		<img src="<?php echo JURL_SITE; ?>/images/joomla_logo_black.jpg" alt="Joomla! Logo" align="middle" />	
		</td>
	</tr>
	<tr>
		<td align="center">
			<h1>
				<?php echo $mosConfig_sitename; ?>
			</h1>
		</td>
	</tr>
	<?php
	if ( $mosConfig_offline == 1 ) {
		?>
		<tr>
			<td width="39%" align="center">
				<h2>
					<?php echo $mosConfig_offline_message; ?>
				</h2>
			</td>
		</tr>
		<?php
	} else if (@$mosSystemError) {
		?>
		<tr>
			<td width="39%" align="center">
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
			<td width="39%" align="center">
			<h2>
				<?php echo JText::_( 'WARNINSTALL' ); ?>
			</h2>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
</body>
</html>