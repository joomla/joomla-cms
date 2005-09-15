<?php
/**
* @version $Id: editor.xstandard.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );
?>
<script type="text/javascript">
<!--
/** Wrapper around the editor specific update function in JavaScript
*/
function updateEditorContents( editorName, newValue ) {
	//TODO: correct call
}
//-->
</script>
<?php

function initEditor() {
	global $mosConfig_live_site, $mainframe;

	if(!$mainframe->get('loadEditor')) {
		return false;
	}
}

function editorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 ) {
	global $mainframe;

	$mainframe->set('loadEditor', true);
?>
<object classid="clsid:0EED7206-1661-11D7-84A3-00606744831D" id="<?php echo $name; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>">
<param name="Value" value="<?php echo $content; ?>" />
</object>
<input type="hidden" name="<?php echo $hiddenField; ?>" id="<?php echo $hiddenField; ?>" value='' />
<?php
}

function getEditorContents( $editorArea, $hiddenfield ) {
	global $mainframe;

	if(!$mainframe->set('loadEditor')) {
		return false;
	}
?>
	document.getElementById('<?php echo $editorArea ; ?>').EscapeUNICODE = true;
	document.getElementById('<?php echo $hiddenfield ; ?>').value = document.getElementById('<?php echo $editorArea ; ?>').value;
<?php
}
?>