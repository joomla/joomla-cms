<?php
/**
* @version $Id: editor.htmlarea2.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

function initEditor() {
	global $mosConfig_live_site, $mainframe;

	if(!$mainframe->get('loadEditor')) {
		return false;
	}
?>
<script language="JavaScript1.2" type="text/JavaScript1.2">
<!--
_editor_url = '<?php echo $mosConfig_live_site; ?>/editor/htmlarea2/';		// URL to htmlarea files
var win_ie_ver = parseFloat(navigator.appVersion.split("MSIE")[1]);
if (navigator.userAgent.indexOf('Mac')	   >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Opera')	 >= 0) { win_ie_ver = 0; }

if (win_ie_ver >= 5.5) {
	document.write('<scr' + 'ipt src="' +_editor_url+ 'editor.js"');
	document.write(' language="Javascript1.2"></scr' + 'ipt>');
} else {
	document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>');
}

/** Wrapper around the editor specific update function in JavaScript
*/
function updateEditorContents( editorName, newValue ) {
	editor_setHTML( editorName, newValue );
}
//-->
</script>
<?php
}

function editorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 ) {
	global $mainframe;

	$mainframe->set('loadEditor', true);

?>
	<textarea name="<?php echo $hiddenField; ?>" id="<?php echo $hiddenField; ?>" cols="<?php echo $col; ?>" rows="<?php echo $row; ?>" style="width:<?php echo $width; ?>; height:<?php echo $height; ?>"><?php echo $content; ?></textarea>
	<script language="JavaScript1.2" defer="defer">
	<!--
	editor_generate('<?php echo $hiddenField ?>');
	//-->
	</script>
<?php
}

function getEditorContents( $editorArea, $hiddenField ) {
	global $mainframe;

	if(!$mainframe->set('loadEditor', true)) {
		return false;
	}
}
?>