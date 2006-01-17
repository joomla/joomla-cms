<?php
/**
* @version $Id: sef.php 1553 2005-12-24 17:04:09Z Saka $
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

// loads english language file by default
//if ($mosConfig_lang=='') {
//	$mosConfig_lang = 'english';
//}
// load language file
//include_once( 'language/' . $mainframe->getCfg('lang') . '.php' );

// backward compatibility
if (!defined( '_404' )) {
	define( '_404', 'We\'re sorry but the page you requested could not be found.' );
}
if (!defined( '_404_RTS' )) {
	define( '_404_RTS', 'Return to site' );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>404 Not Found - <?php echo $mainframe->getCfg('fromname'); ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo _ISO; ?>" />
<style type="text/css">
	body {
		font-family: Arial, Helvetica, Sans Serif;
		font-size: 11px;
		color: #333333;
		background: #ffffff;
		text-align: center;
	}
</style>
</head>
<body>

<h2>
	<?php echo $mainframe->getCfg('fromname'); ?>
</h2>
<h2>
	<?php echo _404;?>
</h2>
<h3>
	<a href="<?php echo $mainframe->getCfg('live_site'); ?>">
		<?php echo _404_RTS;?></a>
</h3>
<br />
Error 404

</body>
</html>