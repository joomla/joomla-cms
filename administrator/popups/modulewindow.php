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
include_once ( $mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php' );

$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
$database->debug( $mosConfig_debug );

$title 	= mosGetParam( $_REQUEST, 'title', 0 );
$css 	= mosGetParam( $_REQUEST, 't', '');
$row 	= null;

$query = "SELECT *"
. "\n FROM #__modules"
. "\n WHERE title = '$title'"
;
$database->setQuery( $query );
$database->loadObject( $row );

$pat		= "src=images";
$replace	= "src=../../images";
$pat2		= "\\\\'";
$replace2	= "'";
$content	= eregi_replace($pat, $replace, $row->content);
$content	= eregi_replace($pat2, $replace2, $row->content);
$title		= eregi_replace($pat2, $replace2, $row->title);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo JText::_( 'Module Preview' ); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"" />
<link rel="stylesheet" href="../../templates/<?php echo $css; ?>/css/template_css.css" type="text/css">
<script>
var content = window.opener.document.adminForm.content.value;
var title = window.opener.document.adminForm.title.value;

content = content.replace('#', '');
title = title.replace('#', '');
content = content.replace('src=images', 'src=../../images');
content = content.replace('src=images', 'src=../../images');
title = title.replace('src=images', 'src=../../images');
content = content.replace('src=images', 'src=../../images');
title = title.replace('src=\"images', 'src=\"../../images');
content = content.replace('src=\"images', 'src=\"../../images');
title = title.replace('src=\"images', 'src=\"../../images');
content = content.replace('src=\"images', 'src=\"../../images');
</script>
</head>

<body style="background-color:#FFFFFF">
<table align="center" width="160" cellspacing="2" cellpadding="2" border="0" height="100%">
<tr>
	<td class="moduleheading"><script>document.write(title);</script></td>
</tr>
<tr>
	<td valign="top" height="90%"><script>document.write(content);</script></td>
</tr>
<tr>
	<td align="center"><a href="#" onClick="window.close()"><?php echo JText::_( 'Close' ); ?></a></td>
</tr>
</table>
</body>
</html>