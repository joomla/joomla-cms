<?php
/**
* @version $Id: index.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// needed to calculate page generation time
$tstart = mosProfiler::getmicrotime();

// xml prolog
echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $_LANG->isoCode();?>">
<head>
<?php mosShowHead_Admin(); ?>
</head>
<body>
<div class="langdirection"> <!-- rtl-change -->
	<div id="wrapper">
		<div id="header">
			<a href="<?php echo $mosConfig_live_site .'/administrator/index2.php'; ?>" target="_self">
				<div id="mambo">
					<img src="templates/mambo_admin_blue/images/header_text.png" alt="<?php echo $_LANG->_( 'Joomla! Logo' ); ?>" border="0"/>
				</div>
			</a>
		</div>
	</div>

	<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="menubackgr">
			<?php mosLoadAdminModules( 'top' );?>
		</td>
		<td class="menubackgr" align="right">
			<div id="wrapper1">
				<?php mosLoadAdminModules( 'header', 2 );?>
			</div>
		</td>
	</tr>
	</table>

	<table width="100%" class="menubar" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="menudottedline" align="left">
			<?php mosLoadAdminModules( 'pathway' );?>
		</td>
	</tr>
	<tr>
		<td class="menudottedline" align="right">
			<?php mosLoadAdminModules( 'toolbar' );?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="menudottedline">
			<?php mosLoadAdminModules( 'user1' );?>
		</td>
	</tr>
	</table>

	<?php mosLoadAdminModules( 'inset' );?>

	<div align="center">
		<div class="main">
			<table width="100%" border="0">
			<tr>
				<td valign="middle" align="center">
					<?php mosMainBody_Admin(); ?>
				</td>
			</tr>
			</table>
		</div>
	</div>

	<table width="99%" border="0">
	<tr>
		<td align="center">
			<div class="smallgrey">
				<?php mosLoadAdminModules( 'footer', -1 );?>
			</div>
		</td>
	</tr>
	</table>

	<?php mosLoadAdminModules( 'debug' );?>
</div> <!-- rtl-change -->
</body>
</html>