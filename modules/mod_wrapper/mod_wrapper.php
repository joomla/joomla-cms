<?php
/**
* @version $Id$
* @package Joomla_1.0.0
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

global $_CONFIG;

$params->def( 'url', '' );
$params->def( 'scrolling', 'auto' );
$params->def( 'height', '200' );
$params->def( 'height_auto', '0' );
$params->def( 'width', '100%' );
$params->def( 'add', '1' );
$params->def( 'name', 'wrapper' );

$url = $params->get( 'url' );
if ( $params->get( 'add' ) ) {
	// adds "http://" if none is set
	if ( !strstr( $url, 'http' ) && !strstr( $url, 'https' ) ) {
		$url = 'http://'. $url;
	}
}

// auto height control
if ( $params->get( 'height_auto' ) ) {
	$load = "window.onload = iFrameHeight;\n";
} else {
	$load = '';
}

?>
<script language="javascript" type="text/javascript">
<?php echo $load; ?>
function iFrameHeight() {
	var h = 0;
	if ( !document.all ) {
		h = document.getElementById('blockrandom').contentDocument.height;
		document.getElementById('blockrandom').style.height = h + 60 + 'px';
	} else if( document.all ) {
		h = document.frames('blockrandom').document.body.scrollHeight;
		document.all.blockrandom.style.height = h + 20 + 'px';
	}
}
</script>
<iframe
id="blockrandom"
name="<?php echo $params->get( 'target' ); ?>"
src="<?php echo $url; ?>"
width="<?php echo $params->get( 'width' ); ?>"
height="<?php echo $params->get( 'height' ); ?>"
scrolling="<?php echo $params->get( 'scrolling' ); ?>"
align="top"
frameborder="0"
class="wrapper<?php echo $params->get( 'moduleclass_sfx' ); ?>">
</iframe>
