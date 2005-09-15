<?php
/**
* @version $Id: none.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onInitEditor', 'botNoEditorInit' );
$_MAMBOTS->registerFunction( 'onGetEditorContents', 'botNoEditorGetContents' );
$_MAMBOTS->registerFunction( 'onEditorArea', 'botNoEditorEditorArea' );

/**
* No WYSIWYG Editor - javascript initialisation
*/
function botNoEditorInit() {
	return <<<EOD
<script type="text/javascript">
	function insertAtCursor(myField, myValue) {
		if (document.selection) {
			// IE support
			myField.focus();
			sel = document.selection.createRange();
			sel.text = myValue;
		} else if (myField.selectionStart || myField.selectionStart == '0') {
			// MOZILLA/NETSCAPE support
			var startPos = myField.selectionStart;
			var endPos = myField.selectionEnd;
			myField.value = myField.value.substring(0, startPos)
				+ myValue
				+ myField.value.substring(endPos, myField.value.length);
		} else {
			myField.value += myValue;
		}
	}
</script>
EOD;
}
/**
* No WYSIWYG Editor - copy editor contents to form field
* @param string The name of the editor area
* @param string The name of the form field
*/
function botNoEditorGetContents( $editorArea, $hiddenField ) {
	return <<<EOD
EOD;
}
/**
* No WYSIWYG Editor - display the editor
* @param string The name of the editor area
* @param string The content of the field
* @param string The name of the form field
* @param string The width of the editor area
* @param string The height of the editor area
* @param int The number of columns for the editor area
* @param int The number of rows for the editor area
* @param int Whether to show the Editor buttons or not - by default set to yes
*/
function botNoEditorEditorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 ) {
	global $mosConfig_live_site, $_MAMBOTS, $option;

	$buttons = '';
	// show buttons
	if ( $showbut ) {
		$buttons = array();
		$results = $_MAMBOTS->trigger( 'onCustomEditorButton' );
		foreach ($results as $result) {
		    $buttons[] = '<img src="'.$mosConfig_live_site.'/mambots/editors-xtd/'.$result[0].'" onclick="insertAtCursor( document.adminForm.'.$hiddenField.', \''.$result[1].'\' )" />';
		}
		$buttons = implode( '', $buttons );
	}

	return <<<EOD
<textarea name="$hiddenField" id="$hiddenField" cols="$col" rows="$row" style="width:$width;height:$height;">$content</textarea>
<br />$buttons
EOD;
}
?>