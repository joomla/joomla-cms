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

// Set flag that this is a parent file
define( "_VALID_MOS", 1 );

require_once( '../includes/auth.php' );

$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
$database->debug( $mosConfig_debug );

$pollid = mosGetParam( $_REQUEST, 'pollid', 0 );
$css = mosGetParam( $_REQUEST, 't', '' );

$query = "SELECT title"
. "\n FROM #__polls"
. "\n WHERE id = $pollid"
;
$database->setQuery( $query );
$title = $database->loadResult();

$query = "SELECT text"
. "\n FROM #__poll_data"
. "\n WHERE pollid = $pollid"
. "\n ORDER BY id"
;
$database->setQuery( $query );
$options = $database->loadResultArray();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Poll Preview</title>
	<link rel="stylesheet" href="../../templates/<?php echo $css; ?>/css/template_css.css" type="text/css">
</head>

<body>
<form>
<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0" >
	<tr>
		<td class="moduleheading" colspan="2"><?php echo $title; ?></td>
	</tr>
	<?php foreach ($options as $text)
	{
		if ($text <> "")
		{?>
		<tr>
			<td valign="top" height="30"><input type="radio" name="poll" value="<?php echo $text; ?>"></td>
			<td class="poll" width="100%" valign="top"><?php echo $text; ?></td>
		</tr>
		<?php }
	} ?>
	<tr>
		<td valign="middle" height="50" colspan="2" align="center"><input type="button" name="submit" value="Vote">&nbsp;&nbsp;<input type="button" name="result" value="Results"></td>
	</tr>
	<tr>
		<td align="center" colspan="2"><a href="#" onClick="window.close()">Close</a></td>
	</tr>
</table>
</form>
</body>
</html>